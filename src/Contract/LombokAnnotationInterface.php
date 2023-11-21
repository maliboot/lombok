<?php
declare(strict_types=1);

namespace MaliBoot\Lombok\Contract;

interface LombokAnnotationInterface extends GetterAnnotationInterface, SetterAnnotationInterface, LoggerAnnotationInterface, ToStringAnnotationInterface, ToCollectionAnnotationInterface, OfAnnotationInterface, ArrayObjectAnnotationInterface
{
}
