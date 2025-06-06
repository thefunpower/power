<?php

namespace Power\TicketPrinter;

class Label
{
    // 标签纸宽度，单位：毫米 (mm)
    public static $width = 40;
    // 标签纸高度，单位：毫米 (mm)
    public static $height = 30;
    // 分辨率，点每毫米 (DPI)，1mm = 8点
    public static $point = 8;
    // 非中文字符（英数字体）的宽高配置，键为字体大小，值为[宽度, 高度]（单位：点）
    public static $abc = [
        12 => [12, 20], // font=12时，宽12点，高20点
        16 => [16, 24],
        10 => [10, 20], // 为font=10添加默认值
    ];
    // 中文字符的宽高配置，键为字体大小，值为[宽度, 高度]（单位：点）
    public static $hanz = [
        12 => [24, 24], // font=12时，宽24点，高24点
        16 => [24, 24],
        10 => [20, 20], // 为font=10添加默认值
    ];
    // 左右边距，单位：点，等于一个font=12的中文字符宽度
    public static $margin = 24;

    /**
     * 设置标签纸大小
     * @param int $width 宽度（毫米）
     * @param int $height 高度（毫米）
     * @return string 返回<SIZE>命令字符串
     */
    public static function setSize($width, $height)
    {
        self::$width = $width;
        self::$height = $height;
        return "<SIZE>{$width},{$height}</SIZE>";
    }

    /**
     * 判断字符串是否包含中文字符
     * @param string $str 输入字符串
     * @return bool 包含中文返回true，否则返回false
     */
    public static function isChinese($str)
    {
        return preg_match("/[\x{4e00}-\x{9fa5}]/u", $str);
    }

    /**
     * 设置打印方向
     * @param int $n 1表示正向出纸，0表示反向出纸
     * @return string 返回<DIRECTION>命令字符串
     */
    public static function setDirection($n = 1)
    {
        return "<DIRECTION>{$n}</DIRECTION>";
    }

    /**
     * 生成文本打印命令
     * @param string $text 文本内容
     * @param array $opt 属性选项（如x, y, font, w, h, r）
     * @return string 返回<TEXT>命令字符串
     */
    public static function setText($text, $opt = [])
    {
        $pro = '';
        foreach ($opt as $k => $v) {
            $pro .= " {$k}=\"{$v}\"";
        }
        return "<TEXT{$pro}>{$text}</TEXT>";
    }

    /**
     * 计算文本宽度（单位：点）
     * @param string $text 文本内容
     * @param int $font 字体大小，默认为12
     * @param int $widthScale 宽度放大倍率，默认为1
     * @return int 文本总宽度
     */
    public static function getTextWidth($text, $font = 12, $widthScale = 1)
    {
        $length = mb_strlen($text, 'utf-8');
        $isChinese = self::isChinese($text);
        $baseWidth = $isChinese ? self::$hanz[$font][0] ?? 24 : self::$abc[$font][0] ?? 12;
        return $length * $baseWidth * $widthScale;
    }

    /**
     * 计算文本高度（单位：点）
     * @param string $text 文本内容
     * @param int $font 字体大小，默认为12
     * @param int $heightScale 高度放大倍率，默认为1
     * @return int 文本总高度
     */
    public static function getTextHeight($text, $font = 12, $heightScale = 1)
    {
        $isChinese = self::isChinese($text);
        $baseHeight = $isChinese ? self::$hanz[$font][1] ?? 24 : self::$abc[$font][1] ?? 20;
        return $baseHeight * $heightScale;
    }

    /**
     * 按最大宽度分割文本为多行
     * @param string $text 文本内容
     * @param int $font 字体大小
     * @param int $widthScale 宽度
     * @param int $maxWidth 最大可用宽度（点）
     * @return array 分割后的文本行数组
     */
    private static function wrapText($text, $font, $widthScale, $maxWidth)
    {
        $lines = [];
        $currentLine = '';
        $currentWidth = 0;
        $isChinese = self::isChinese($text);
        $baseWidth = $isChinese ? self::$hanz[$font][0] ?? 24 : self::$abc[$font][0] ?? 12;
        $charWidth = $baseWidth * $widthScale;

        for ($i = 0; $i < mb_strlen($text, 'utf-8'); $i++) {
            $char = mb_substr($text, $i, 1, 'utf-8');
            $charWidthScaled = self::getTextWidth($char, $font, $widthScale);
            if ($currentWidth + $charWidthScaled <= $maxWidth) {
                $currentLine .= $char;
                $currentWidth += $charWidthScaled;
            } else {
                $lines[] = $currentLine;
                $currentLine = $char;
                $currentWidth = $charWidthScaled;
            }
        }

        if ($currentLine !== '') {
            $lines[] = $currentLine;
        }

        return $lines;
    }

    /**
     * 获取文本属性（x, y坐标等）基于对齐方式
     * @param array $item 文本项配置（包含title, font, widthScale, heightScale, align, r, x, y等）
     * @param int $availableWidth 可用宽度（用于对齐计算）
     * @param int $yOffset 当前y坐标偏移（当未指定y值时使用）
     * @param bool $allowWrap 是否允许换行（多列行设为false）
     * @return array 返回包含每行文本的属性数组
     */
    public static function getAttr($item, $availableWidth, $yOffset, $allowWrap = true)
    {
        $text = $item['title'] ?? '';
        $font = $item['font'] ?? 12;
        $widthScale = $item['widthScale'] ?? 1;
        $heightScale = $item['heightScale'] ?? 1;
        $align = $item['align'] ?? 'left';
        $rotation = $item['r'] ?? 0;

        // 先定义边距和尺寸变量
        $fullWidth = self::$width * self::$point; // 320点
        $fullHeight = self::$height * self::$point; // 240点
        $margin = self::$margin; // 24点边距
        $effectiveWidth = $availableWidth - 2 * $margin; // 减去左右边距

        // 如果指定了x或y值（单位毫米），转换为点
        $x = isset($item['x']) ? (int)($item['x'] * self::$point) : $margin;
        $y = isset($item['y']) ? (int)($item['y'] * self::$point) : $yOffset;

        $textHeight = self::getTextHeight($text, $font, $heightScale);

        // 处理换行（仅当允许换行时）
        $lines = $allowWrap ? self::wrapText($text, $font, $widthScale, $effectiveWidth) : [$text];
        $attrs = [];

        foreach ($lines as $index => $line) {
            $lineWidth = self::getTextWidth($line, $font, $widthScale);

            // 如果指定了x坐标，直接使用（毫米已转点）
            if (isset($item['x'])) {
                $x = (int)($item['x'] * self::$point);
            } else {
                // 否则根据对齐方式计算x坐标
                switch ($align) {
                    case 'center':
                        $x = (int)($margin + ($effectiveWidth - $lineWidth) / 2);
                        break;
                    case 'right':
                        $x = (int)($margin + ($effectiveWidth - $lineWidth));
                        break;
                    case 'left':
                    default:
                        $x = $margin;
                        break;
                }
            }

            // 每行y坐标（如果指定了y，直接使用；否则递增）
            $lineY = isset($item['y']) ? (int)($item['y'] * self::$point) : $y + ($index * $textHeight);

            // 确保坐标在有效范围内
            $x = max($margin, min($x, $fullWidth - $lineWidth - $margin));
            $lineY = max(0, min($lineY, $fullHeight - $textHeight));

            $attrs[] = [
                'x' => $x,
                'y' => $lineY,
                'font' => $font,
                'w' => $widthScale,
                'h' => $heightScale,
                'r' => $rotation,
                'text' => $line
            ];
        }

        return $attrs;
    }

    /**
     * 生成条形码或LOGO打印命令（通用方法）
     * @param string $type 标签类型（barcode128/barcode39/logo）
     * @param string $content 内容（条形码内容，LOGO可为空）
     * @param array $opt 属性选项（如x, y, h, s, r, n, w等）
     * @return string 返回标签命令字符串
     */
    public static function setSpecialElement($type, $content = '', $opt = [])
    {
        $pro = '';
        foreach ($opt as $k => $v) {
            $pro .= " {$k}=\"{$v}\"";
        }

        $tagMap = [
            'barcode128' => 'BC128',
            'barcode39' => 'BC39',
            'logo' => 'LOGO'
        ];

        $tag = $tagMap[$type] ?? '';
        if (!$tag) {
            return ''; // 无效类型返回空字符串
        }

        return "<{$tag}{$pro}>{$content}</{$tag}>";
    }

    /**
     * 处理布局数据并生成打印命令
     * @param array $layoutData 布局数据（多行，每行可包含单列或多列）
     * @return string 返回完整的打印命令字符串
     */
    public static function run($layoutData = [])
    {
        $output = "";
        $output .= self::setDirection(1) . "\n"; // 设置打印方向为正向

        $yOffset = 10; // 初始y坐标偏移，留10点边距
        $fullWidth = self::$width * self::$point; // 总宽度320点
        $lineSpacing = 10; // 行间距10点
        $margin = self::$margin; // 24点边距

        foreach ($layoutData as $row) {
            if (!is_array($row) || empty($row)) {
                continue; // 跳过空行或无效行
            }

            // 处理特殊标签（条形码/LOGO）
            if (isset($row['type'])) {
                $content = $row['code'] ?? ($row['content'] ?? '');
                $output .= self::setSpecialElement($row['type'], $content, $row) . "\n";
                continue;
            }

            $maxHeight = 0;
            $isSingleItem = !isset($row[0]) || !is_array($row[0]);

            if ($isSingleItem) {
                // 单列行，允许换行
                $attrsList = self::getAttr($row, $fullWidth, $yOffset, true);
                foreach ($attrsList as $attrs) {
                    $output .= self::setText($attrs['text'], array_diff_key($attrs, ['text' => ''])) . "\n";
                    $maxHeight = max($maxHeight, self::getTextHeight($attrs['text'], $attrs['font'], $attrs['h']));
                }
                // 如果未指定y值，更新yOffset
                if (!isset($row['y'])) {
                    $yOffset += $maxHeight + $lineSpacing;
                }
            } else {
                // 多列行，不允许换行
                $numColumns = count($row);
                $columnWidth = $fullWidth / $numColumns; // 每列宽度
                $effectiveColumnWidth = $columnWidth - 2 * $margin; // 每列有效宽度

                foreach ($row as $index => $item) {
                    // 创建新数组以避免修改原始数据
                    $item = array_merge([], $item);
                    // 如果未指定align，设置默认对齐方式
                    if (!isset($item['align'])) {
                        if ($numColumns == 2) {
                            // 两列：第一列左对齐，第二列右对齐
                            $item['align'] = $index == 0 ? 'left' : 'right';
                        } else if ($numColumns == 3) {
                            // 三列：第一列左对齐，第二列居中，第三列右对齐
                            $item['align'] = $index == 0 ? 'left' : ($index == 1 ? 'center' : 'right');
                        }
                    }
                    $attrsList = self::getAttr($item, $columnWidth, $yOffset, false); // 禁用换行
                    foreach ($attrsList as $attrs) {
                        // 调整x坐标以适应列位置
                        $attrs['x'] = (int)($attrs['x'] + ($index * $columnWidth)); // 保留1位小数
                        $output .= self::setText($attrs['text'], array_diff_key($attrs, ['text' => ''])) . "\n";
                        $maxHeight = max($maxHeight, self::getTextHeight($attrs['text'], $attrs['font'], $attrs['h']));
                    }
                }
                // 如果未指定y值，更新yOffset
                if (!isset($row[0]['y'])) {
                    $yOffset += $maxHeight + $lineSpacing;
                }
            }
        }

        return $output;
    }
}
