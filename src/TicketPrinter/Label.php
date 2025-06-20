<?php

namespace Power\TicketPrinter;

class Label
{
    public static $width = 40; // 毫米
    public static $height = 30; // 毫米
    public static $point = 8; // 点每毫米
    public static $abc = [12 => [8, 12], 13 => [16, 24], 14 => [24, 32]]; // 非中文
    public static $hanz = [12 => [24, 24], 13 => [28, 28], 14 => [32, 32]]; // 中文

    public static function setSize($width, $height)
    {
        self::$width = $width;
        self::$height = $height;
        return "<SIZE>$width,$height</SIZE>";
    }

    public static function isChinese($char)
    {
        return preg_match("/[\x{4e00}-\x{9fa5}]/u", $char);
    }

    public static function setDirection($n = 1)
    {
        return "<DIRECTION>$n</DIRECTION>";
    }

    public static function setText($text, $opt)
    {
        $pro = '';
        foreach ($opt as $k => $v) $pro .= " $k=\"$v\"";
        return "<TEXT$pro>$text</TEXT>";
    }

    public static function getTextWidth($text, $font = 12, $widthScale = 1)
    {
        $width = 0;
        for ($i = 0; $i < mb_strlen($text, 'utf-8'); $i++) {
            $char = mb_substr($text, $i, 1, 'utf-8');
            $baseWidth = (self::isChinese($char) || $char === '：' || $char === ':') ? (self::$hanz[$font][0] ?? 24) : (self::$abc[$font][0] ?? 8);
            $width += $baseWidth * $widthScale;
        }
        return $width;
    }

    public static function getTextHeight($text, $font = 12, $heightScale = 1)
    {
        $baseHeight = self::isChinese(mb_substr($text, 0, 1, 'utf-8')) ? (self::$hanz[$font][1] ?? 24) : (self::$abc[$font][1] ?? 12);
        return $baseHeight * $heightScale;
    }

    private static function getAttr($item, $yOffset)
    {
        $text = $item['title'] ?? '';
        $font = $item['font'] ?? 12;
        $widthScale = $item['widthScale'] ?? 1;
        $heightScale = $item['heightScale'] ?? 1;

        $fullWidth = self::$width * self::$point; // 320
        $fullHeight = self::$height * self::$point; // 240
        $baseY = isset($item['y']) ? ($item['y'] * self::$point) : $yOffset;
        $textHeight = self::getTextHeight($text, $font, $heightScale);
        $lineWidth = self::getTextWidth($text, $font, $widthScale);

        $x = isset($item['x']) ? $item['x'] * self::$point : 0;
        $x = max(0, min($x, $fullWidth - $lineWidth));
        $y = max(0, min($baseY, $fullHeight - $textHeight));

        return [[
            'x' => round($x, 1),
            'y' => round($y, 1),
            'font' => $font,
            'w' => $widthScale,
            'h' => $heightScale,
            'text' => $text
        ], $textHeight];
    }

    public static function run($layoutData = [])
    {
        $output = self::setDirection(1) . "\n";
        $yOffset = 8; // 初始1mm

        foreach ($layoutData as $row) {
            if (!is_array($row) || empty($row) || !isset($row['title'])) continue;
            [$attrs, $textHeight] = self::getAttr($row, $yOffset);
            $output .= self::setText($attrs['text'], array_diff_key($attrs, ['text' => ''])) . "\n";
            if (!isset($row['y'])) $yOffset += $textHeight + 8;
        }

        return $output;
    }
}
