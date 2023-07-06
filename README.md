# Dinery

PSR-11を実装したDIコンテナ。  
コンストラクタの引数を解析し、再帰的に依存関係を解決する。

```PHP
class TestAction
{
    public function __construct(
        protected TestDomain $domain,
        protected TestResponder $responder
    ) {
    }
}

class TestDomain
{
}

class TestResponder
{
}

// TestAtcionクラスがTestDomainクラスとTestResponderに依存している関係を解決してインスタンス化する
$di = new Container();

$test_action = $di->get(TestAtcion::class);
print_r($test_action);
/* 
TestAtcion Object
(
    [domain:TestAtcion:protected] => TestDomain Object
        (
        )

    [responder:TestAtcion:private] => TestResponder Object
        (
        )
)
*/
```

## Attributesを用いたインジェクションの指定

引数の型が具象クラス名ではなく、抽象クラスやインターフェイスで指定されている場合、
事前に`Container::add`で登録されていなければ、コンテナが依存を解決することができない。

`#[Inject]`で具象クラス名を指定することで、そのクラスのインスタンスをインジェクションすることができる。

```PHP
class TestAction {public function __construct(
        #[Inject(TestDomain::class)]
        protected DomainInterface $domain,
        
        #[Inject(TestResponder::class)]
        protected ResponderInterface $responder
    ) {
    }
}
```