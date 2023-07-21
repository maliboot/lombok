## Lombok

### 简介
这是一个通过`注解`来减少`重复代码`的工具

### 依赖
* `hyperf/di`

### 安装
- PHP包安装（必须）：`composer require maliboot/lombok`
- PHPStorm插件支持（可选）：`Maliboot Support`，[下载传送门]()

### 使用
#### 基本使用
使用`lombok`前，我们实现一个`Getter`功能, 需要写如下代码：
```
// app/Foo.php
<?php
declare(strict_types=1);

namespace App;

class Foo
{
    private int $id = 1;
    
    public function getId(): int
    {
        return $this->id;
    }
}

var_dump((new Foo)->getId()); // output: 1
```

使用`lombok`后，我们只需要添加一个`Getter`注解，皆可省掉以下方法：
```
// app/Foo.php
<?php
declare(strict_types=1);

namespace App;

use Maliboot\Lombok\Annotation\Getter;

// 当Getter为类注解时，会为所有的类属性添加Getter方法，无需要一个一个添加
// #[Getter]
class Foo
{
    // Getter为类属性注解时
    #[Getter]
    private int $id;
}
var_dump((new Foo)->getId()); // output: 1
```

#### 进阶用法
###### 1、可用注解如下

| 注解                                         | 功能                                                                      |
|:-------------------------------------------|:------------------------------------------------------------------------|
| `\Maliboot\Lombok\Annotation\Getter`       | 生成类属性`Getter`方法                                                         |
| `\Maliboot\Lombok\Annotation\Setter`       | 生成类属性`Setter`方法                                                         |
| `\Maliboot\Lombok\Annotation\SetterGetter` | 集成`Setter`与`Getter`功能                                                   |
| `\Maliboot\Lombok\Annotation\Logger`       | 生成类属性`public \Psr\Log\LoggerInterface $logger`                          |
| `\Maliboot\Lombok\Annotation\ToArray`      | 生成类方法`public static function toArray(object $class): array`             |
| `\Maliboot\Lombok\Annotation\ToCollection` | 生成类方法`public static function toCollection(object $class): Collection`方法 |
| `\Maliboot\Lombok\Annotation\Lombok`       | 集成以上所有注解功能                                                              |

###### 2、自定义注解
* 当我们需要自定义一个与`Setter`功能一样的注解时。可以 `Getter`为例，创建一个`MyGetter`注解：
```
<?php

namespace App/Annotation;

use Attribute;
use Hyperf\Di\Annotation\AbstractAnnotation;
use Maliboot\Lombok\contract\GetterAnnotationInterface;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_PROPERTY)]
class MyGetter extends AbstractAnnotation implements GetterAnnotationInterface
{
}
```

* 创建完注解后，只要此注解继承了`\Maliboot\Lombok\contract\GetterAnnotationInterface`，此注解将自动拥有原`\Maliboot\Lombok\Annotation\Getter`的功能
```
// app/Foo.php
<?php
declare(strict_types=1);

namespace App;

use Maliboot\Lombok\Annotation\Getter;

// MyGetter为类属性注解时
// #[MyGetter]
class Foo
{
    // MyGetter为类属性注解时
    #[MyGetter]
    private int $id;
}
var_dump((new Foo)->getId()); // output: 1
```

###### 3、自定义组合注解功能
当然，同时我们需要自定义一个具有`Setter`,`Getter`, `Logger`等多功能的注解，并且可以自定义组合这些功能时，同理，只需要在某注解上继续继承相应的注解接口皆可。可用的接口如下

| 注解接口                                                        | 功能                                                                      |
|:------------------------------------------------------------|:------------------------------------------------------------------------|
| `\Maliboot\Lombok\contract\GetterAnnotationInterface`       | 生成类属性`Getter`方法                                                         |
| `\Maliboot\Lombok\contract\SetterAnnotationInterface`       | 生成类属性`Setter`方法                                                         |
| `\Maliboot\Lombok\contract\LoggerAnnotationInterface`       | 生成类属性`public \Psr\Log\LoggerInterface $logger`                          |
| `\Maliboot\Lombok\contract\ToArrayAnnotationInterface`      | 生成类方法`public static function toArray(object $class): array`             |
| `\Maliboot\Lombok\contract\ToCollectionAnnotationInterface` | 生成类方法`public static function toCollection(object $class): Collection`方法 |

###### 4、自定义`日志实例`、`toArray()`、`toCollection()`实现
* 目前当前功能实现在`\Maliboot\Lombok\Delegate`内
* 4.1 `hyperf`中自定义实现
  * 自定义实现类，如`\App\Foo`，并继承`\Maliboot\Lombok\contract\DelegateInterface`
  * 绑定依赖，在`config.dependencies`内配置`\Maliboot\Lombok\contract\DelegateInterface => \App\Foo::class`
* 4.2 非`hyperf`中自定义实现
  * todo

#### 注意事项
* 为了保证代码安全，当功能注解生成的代码与原代码冲突时，将放弃生成相应代码，以原代码为准。如`Setter`、`toArray`方法已存在，`public $logger`类属性已经存在等等