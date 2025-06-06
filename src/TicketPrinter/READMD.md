# 飞蛾小票打印

~~~ 
 

$data = [
  [
    "title"=>'标题',
    'tag'=>'cb|br', 
  ],
  [
    "title"=>'123465',
    'tag'=>'code_int|br', 
  ],
  [
    'tag'=>'line|br'
  ],
  [
     'top'=>[
        'title'=>'名称|*',
        'price'=>'单价|2',
        'num'=>'数量|1', 
     ],
     'list'=>[
        [
            'title'=>'酸菜鱼',
            'price'=>'100.4',
            'num'=>'10',
        ],
        [
            'title'=>'可乐鸡翅+蒜蓉蒸扇贝',
            'price'=>'10.3',
            'num'=>'6',
        ],
        [
            'title'=>'紫苏焖鹅+梅菜肉饼+椒盐虾+北京烤鸭',
            'price'=>'10.0',
            'num'=>'8',
        ],
     ]
  ], 
];

$client = new \Power\TicketPrinter\Feie; 
$client->user = '';
$client->ukey = '';
$client->sn = '';
$client->backUrl = Env::getDomain();

$client->print($data);
~~~


# Base类说明

~~~
$s = new \Power\Base;    
$data = [
  [
    "title"=>'标题',
    'tag'=>'cb|br', 
  ],
  [
    "title"=>'123465',
    'tag'=>'code_int|br', 
  ],
  [
    'tag'=>'line|br'
  ],
  [
     'top'=>[
        'title'=>'名称|*',
        'price'=>'单价|2',
        'num'=>'数量|1', 
     ],
     'list'=>[
        [
            'title'=>'酸菜鱼',
            'price'=>'100.4',
            'num'=>'10',
        ],
        [
            'title'=>'可乐鸡翅+蒜蓉蒸扇贝',
            'price'=>'10.3',
            'num'=>'6',
        ],
        [
            'title'=>'紫苏焖鹅+梅菜肉饼+椒盐虾+北京烤鸭',
            'price'=>'10.0',
            'num'=>'8',
        ],
     ]
  ], 
];
$str = $s->parse($data);
echo $str;

每一个打印接口需要实现
protected function do_print_label($set=[] , $sn,$content,$times){
}
protected function do_print_58mm($set,$sn,$content,$times){
}
~~~



## 飞蛾标签机

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
     
    // 条形码（Code128）
    /*[
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
    ],*/
    [
        ['title' => '孙先生', 'font' => 12,'y' => 22 , 'widthScale' => 1, 'heightScale' => 1],
        ['title' => '5213', 'font' => 12,'y' => 22 , 'widthScale' => 1, 'heightScale' => 1, 'align' => 'right',],
    ],

    // 第四行：底部对齐，指定y=25mm (200点)
    [
        'title' => date('Y-m-d H:i:s'),
        'font' => 10,
        'y' => 25, 
    ],
];
$data = Label::run($layoutData);
~~~
