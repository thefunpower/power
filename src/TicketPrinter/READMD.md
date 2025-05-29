# 小票打印

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