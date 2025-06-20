<?php
/**
 * 飞蛾标签机
 * https://help.feieyun.com/home/doc/zh;nav=1-2
 */
namespace Power\TicketPrinter;

class Label
{
    public static $width = 40; // 毫米
    public static $height = 30; // 毫米
    public static $point = 8; // 点每毫米
    public static $abc = [12 => [8, 12]]; // 非中文
    public static $hanz = [12 => [24, 24]]; // 中文
    public static $abc_w_sizes = [12 => 1.58]; // 非中文宽度 mm
    public static $hanz_w_sizes = [12 => 3]; // 中文宽度 mm
    public static $abc_h_sizes = [12 => 1.58]; // 非中文高度 mm
    public static $hanz_h_sizes = [12 => 3]; // 中文高度 mm

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

    public static function getTextWidth($text, $font = 12, $w = 1)
    {
        $width = 0;
        for ($i = 0; $i < mb_strlen($text, 'utf-8'); $i++) {
            $char = mb_substr($text, $i, 1, 'utf-8');
            $baseWidth = (self::isChinese($char) || $char === '：' || $char === ':') ? (self::$hanz[$font][0] ?? 24) : (self::$abc[$font][0] ?? 8);
            $width += $baseWidth * $w;
        }
        return $width;
    }

    public static function getTextWidthMm($text, $font = 12, $w = 1)
    {
        $width = 0;
        for ($i = 0; $i < mb_strlen($text, 'utf-8'); $i++) {
            $char = mb_substr($text, $i, 1, 'utf-8');
            $baseWidth = (self::isChinese($char) || $char === '：' || $char === ':') ? (self::$hanz_w_sizes[$font] ?? 3) : (self::$abc_w_sizes[$font] ?? 1.58);
            $width += $baseWidth * $w;
        }
        return $width;
    }

    public static function getTextHeight($text, $font = 12, $h = 1)
    {
        $baseHeight = self::isChinese(mb_substr($text, 0, 1, 'utf-8')) ? (self::$hanz[$font][1] ?? 24) : (self::$abc[$font][1] ?? 12);
        return $baseHeight * $h;
    }

    private static function wrapText($text, $font, $w)
    {
        $lines = [];
        $currentLine = '';
        $currentWidth = 0;
        $maxWidth = (self::$width - 2) * self::$point; // 38mm = 304 点

        for ($i = 0; $i < mb_strlen($text, 'utf-8'); $i++) {
            $char = mb_substr($text, $i, 1, 'utf-8');
            $charWidth = (self::isChinese($char) || $char === '：' || $char === ':') ? (self::$hanz_w_sizes[$font] ?? 3) : (self::$abc_w_sizes[$font] ?? 1.58);
            $charWidth *= $w * self::$point; // 毫米转点

            if ($currentWidth + $charWidth <= $maxWidth) {
                $currentLine .= $char;
                $currentWidth += $charWidth;
            } else {
                $lines[] = $currentLine;
                $currentLine = $char;
                $currentWidth = $charWidth;
            }
        }
        if ($currentLine) $lines[] = $currentLine;
        return $lines;
    }

    private static function getLineHeight($text, $font, $h)
    {
        for ($i = 0; $i < mb_strlen($text, 'utf-8'); $i++) {
            $char = mb_substr($text, $i, 1, 'utf-8');
            if (self::isChinese($char)) {
                return (self::$hanz_h_sizes[$font] ?? 3) * $h * self::$point; // 24 点
            }
        }
        return (self::$abc_h_sizes[$font] ?? 1.58) * $h * self::$point; // 12.64 点
    }

    private static function getAttr($item, $yOffset)
    {
        $text = $item['title'] ?? '';
        $font = $item['font'] ?? 12;
        $w = $item['w'] ?? 1;
        $h = $item['h'] ?? 1;

        $fullWidth = self::$width * self::$point; // 320
        $maxY = (self::$height - 5) * self::$point; // 25mm = 200 点
        $baseY = isset($item['y']) ? max($item['y'] * self::$point, $yOffset) : $yOffset;
        $lines = isset($item['title']) ? self::wrapText($text, $font, $w) : [$text];
        $attrs = [];

        $y = $baseY;
        foreach ($lines as $line) {
            $lineWidth = self::getTextWidth($line, $font, $w);
            $lineHeight = self::getLineHeight($line, $font, $h);

            $x = isset($item['x']) ? $item['x'] * self::$point : 0;
            $x = max(0, min($x, $fullWidth - $lineWidth));
            $y = max(0, min($y, $maxY - $lineHeight));

            $attrs[] = [
                'x' => (int)$x,
                'y' => (int)$y,
                'font' => $font,
                'w' => $w,
                'h' => $h,
                'text' => $line
            ];

            $y += $lineHeight;
        }

        return [$attrs, $y - $baseY]; // 总高度
    }

    public static function run($layoutData = [])
    {
        $output = self::setDirection(1) . "\n";
        $yOffset = self::$point; // 初始1mm
        $lastX = 0; // 上一行 x
        $lastWidth = 0; // 上一行宽度（毫米）

        foreach ($layoutData as $row) {
            if (!is_array($row) || empty($row)) continue;

            if (isset($row['type'])) {
                $newAttr = [];
                if (isset($row['x'])) $newAttr['x'] = (int)($row['x'] * self::$point);
                if (isset($row['y'])) $newAttr['y'] = (int)(min($row['y'] * self::$point, (self::$height - 5) * self::$point));
                $type = $row['type'];
                $text = $row['text'] ?? "";
                unset($row['type'], $row['text']);
                $newRow = array_merge($row, $newAttr);
                $str = '';
                foreach ($newRow as $k => $v) $str .= " $k=\"$v\"";
                if ($type == 'LOGO') {
                    $output .= "<$type$str>\n";
                } else {
                    $output .= "<$type$str>$text</$type>\n";
                }
                continue;
            }

            if (!isset($row['title'])) continue;

            // 计算当前行宽度（毫米）
            $font = $row['font'] ?? 12;
            $w = $row['w'] ?? 1;
            $currentWidthMm = self::getTextWidthMm($row['title'], $font, $w);
            $currentX = isset($row['x']) ? $row['x'] : 0;

            // 若 x >= 上一行 x + 宽度，重置 yOffset
            if ($currentX >= $lastX + $lastWidth) {
                $yOffset = self::$point; // 重置为初始 1mm
            }

            [$attrs, $textHeight] = self::getAttr($row, $yOffset);
            foreach ($attrs as $attr) {
                $output .= self::setText($attr['text'], array_diff_key($attr, ['text' => ''])) . "\n";
            }
            $yOffset += $textHeight + self::$point * 2; // 累加总高度

            // 更新上一行信息
            $lastX = $currentX;
            $lastWidth = $currentWidthMm;
        }

        return $output;
    }
}
