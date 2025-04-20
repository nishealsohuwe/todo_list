<?php
    require __DIR__ . '/../vendor/autoload.php';
    use OpenApi\Generator;

    $openapi = Generator::scan([
        __DIR__ . '/../src',
        __DIR__ . '/../config/swagger-annotations.php'
    ]);

    file_put_contents(__DIR__ . '/../public/swagger.yaml', $openapi->toYaml());

    echo "Документация успешно сгенерирована!\n";