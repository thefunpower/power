# Power

安装

~~~
"thefunpower/power": "dev-main"
~~~ 


## Unzip


解压zip、7z、gz、tar、bz2包

~~~
yum -y install p7zip unar unzip
~~~

使用

~~~
Power\Unzip::run($input, $output);
~~~

`$input` 为待解压文件完整路径

`$output` 为解压后文件存放目录


## Label

~~~
use Power\TicketPrinter\Label;

$layoutData = [
    // 第一行：三列，未指定y，自动布局
    [
        ['title' => '订单001', 'font' => 12, 'widthScale' => 1, 'heightScale' => 1],
        ['title' => '五号桌', 'font' => 12, 'widthScale' => 1, 'heightScale' => 1, 'align' => 'center',],
        ['title' => '1/5', 'font' => 12, 'widthScale' => 1, 'heightScale' => 1, 'align' => 'right',]
    ],
    // 第二行：单列，指定y=15mm (120点)，居中
    [
        'title' => '可乐鸡翅',
        'font' => 12,
        'align' => 'center',
        'widthScale' => 2,
        'heightScale' => 2,
        'y' => 8 // 毫米
    ],
    // 第三行：单列，未指定y，自动布局
    [
        'title' => '备注：不要加辣！！！',
        'font' => 12,
        'align' => 'left',
        'y' => 20 // 毫米
    ],
    // 第四行：底部对齐，指定y=25mm (200点)
    [
        'title' => date('Y-m-d H:i:s'),
        'font' => 10,
        'y' => 25,
        'x' => 20,
    ],
    // 条形码（Code128）
    [
        'type' => 'barcode128',
        'code' => '12345678',
        'x' => 10,
        'y' => 100,
        'h' => 80,
        's' => 1,
        'r' => 0,
        'n' => 1,
        'w' => 1
    ],
    // 条形码（Code39）
    [
        'type' => 'barcode39',
        'code' => 'ABCD1234',
        'x' => 10,
        'y' => 150,
        'h' => 60
    ],
    // LOGO
    [
        'type' => 'logo',
        'x' => 200,
        'y' => 50
    ]
];
$data = Label::run($layoutData);
~~~

### 开源协议 

[Apache License 2.0](LICENSE)
