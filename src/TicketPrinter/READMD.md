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

https://help.feieyun.com/home/doc/zh;nav=1-2

`x` `y`的值是`mm`不是像素。

## 模板一

~~~
use Power\TicketPrinter\Label;
$layoutData = [
    ['title' => 'A103', 'x' => 1,'y'=>1,],
    ['title' => '06-20/14:18', 'x' => 20,'y'=>1,],
    ['title' => '生椰拿铁', 'x' => 1, 'y' => 6, 'w' => 1, 'h' => 2],
    ['title' => '常温 无糖', 'x' => 1, 'y' => 13],
    ['title' => '手机尾号:2737', 'x' => 18, 'y' => 25],
]; 
$layoutData[] = [ 
    'type' => 'QR', 
    'text' => 'QR123',
    'x' => 23,
    'y' => 6,
    'e'=>'L',
    'w' => 5,
];
$data = Label::run($layoutData); 
~~~

## 支持自动换行

~~~
use Power\TicketPrinter\Label;
$layoutData = [
    ['title' => 'A103', 'x' => 1,'y'=>1,],
    ['title' => '06-20/14:18', 'x' => 20,'y'=>1,],
    ['title' => '生椰拿铁生椰拿铁生椰拿铁生椰拿铁生椰拿铁', 'x' => 1, 'y' => 6, 'w' => 1, 'h' => 2],
    ['title' => '常温 无糖', 'x' => 1, 'y' => 13],
    ['title' => '手机尾号:1111', 'x' => 18, 'y' => 25],
]; 

$data = Label::run($layoutData); 

~~~



## 测试一行可以打多少个字。

~~~
use Power\TicketPrinter\Label;

$layoutData[] = [
    'title' =>"0123456789012345678901234",
    'font' => 12,
    'x'=>0,
    'y' => 2  
];

$layoutData[] = [
    'title' =>"我是中文我是中文我是中文啦",
    'font' => 12,
    'x'=>0,
    'y' => 10,
    'w'=>2,
    'h'=>2,
];

$data = Label::run($layoutData);
~~~


主要依赖 `x` `y`来定位，没有自动换行功能。需要用户自己计算x y的值。

##  演示二维码

~~~
$layoutData[] = [ 
    'type' => 'BC128',
    'text' => '12345678',
    'x' => 25,
    'y' => 6,
    'h' => 80,
    's' => 1,
    'r' => 0,
    'n' => 1,
    'w' => 1 
];

$layoutData[] = [ 
    'type' => 'LOGO', 
    'x' => 25,
    'y' => 6,
];

$layoutData[] = [ 
    'type' => 'QR', 
    'text' => 'QR123',
    'x' => 23,
    'y' => 6,
    'e'=>'L',
    'w' => 5,
];
~~~

