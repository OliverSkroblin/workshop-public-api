<?php

namespace Scripts\Examples;

use Doctrine\DBAL\Connection;
use Shopware\Core\Content\Product\ProductCollection;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\Search\AggregationResult\AggregationResultCollection;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\EntitySearchResult;
use Shopware\Core\Framework\Extensions\Extension;
use Shopware\Core\Framework\Extensions\ExtensionDispatcher;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

require_once __DIR__ . '/../examples/base-script.php';

$env = 'prod'; // by default, kernel gets booted in dev

$kernel = require __DIR__ . '/../boot/boot.php';

/**
 * @extends Extension<string[]>
 */
class DetectIdsExtension extends Extension
{
    public const NAME = 'workshop.detect-ids';
}

/**
 * @extends Extension<EntitySearchResult<ProductCollection>>
 */
class FetchDataExtension extends Extension
{
    public const NAME = 'workshop.fetch-data';

    public function __construct(public readonly array $ids)
    {
    }
}

/**
 * @extends Extension<void>
 */
class RenderExtension extends Extension
{
    public const NAME = 'workshop.render';

    public function __construct(public readonly EntitySearchResult $products)
    {
    }
}

class NotifiyRenderedExtension extends Extension
{
    public const NAME = 'workshop.notify-rendered';

    public function __construct(public readonly string $html)
    {
    }
}

#[AsEventListener(event: 'detect-ids.post', method: 'ids')]
#[AsEventListener(event: 'workshop.render.pre', method: 'render')]
class WorkshopListener
{
    public function ids(DetectIdsExtension $extension): void
    {
        $extension->result[] = '019053b6d105729c84e968d8091ff62c';
    }

    public function render(RenderExtension $extension)
    {
        foreach ($extension->products as $product) {
            $data[] = [
                'id' => $product->getId(),
                'active' => $product->getActive()
            ];
        }

        echo json_encode($data, JSON_PRETTY_PRINT);

        $extension->stopPropagation();
    }
}

class Main extends BaseScript
{
    /**
     * @public use it, but use extension points to manipulate the flow inside
     *
     * @return void
     */
    public function run()
    {
        $dispatcher = $this->getContainer()->get(ExtensionDispatcher::class);

        $ids = $dispatcher->publish(
            name: DetectIdsExtension::NAME,
            extension: new DetectIdsExtension(),
            function: $this->detectIds(...)
        );

        $products = $dispatcher->publish(
            name: FetchDataExtension::NAME,
            extension: new FetchDataExtension($ids),
            function: $this->fetch(...)
        );

        $dispatcher->publish(
            name: RenderExtension::NAME,
            extension: new RenderExtension($products),
            function: $this->render(...)
        );
    }

    private function render(EntitySearchResult $products): void
    {
        $io = new SymfonyStyle(new ArrayInput([]), new ConsoleOutput());

        $table = new Table($io);

        $table->setHeaders(['ID', 'Active']);

        foreach ($products as $product) {
            $table->addRow([
                $product->getId(),
                $product->getActive() ? 'Yes' : 'No'
            ]);
        }

        $html = $table->render();

        $this->getContainer()->get(ExtensionDispatcher::class)
            ->publish(
                name: NotifiyRenderedExtension::NAME,
                extension: new NotifiyRenderedExtension($html),
                function: fn(NotifiyRenderedExtension $extension) => null
            );
    }

    private function fetch(array $ids): EntitySearchResult
    {
        if (empty($ids)) {
            return new EntitySearchResult('product', 0, new ProductCollection(), new AggregationResultCollection(), new Criteria(), Context::createCLIContext());
        }

        return  $this->getContainer()->get('product.repository')
            ->search(new Criteria($ids), Context::createCLIContext());
    }

    private function detectIds(): array
    {
        return $this->getContainer()->get(Connection::class)
            ->fetchFirstColumn('SELECT LOWER(HEX(id)) FROM product LIMIT 10');
    }
}

$script = (new Main($kernel));

$kernel->getContainer()->get('event_dispatcher')
    ->addListener(
        'workshop.detect-ids.post',
        function(DetectIdsExtension $extension) {
            $extension->result[] = '019053b6d105729c84e968d8091ff62c';
            $extension->stopPropagation();
        }
);

// replace render event and print out json
$kernel->getContainer()->get('event_dispatcher')
    ->addListener(
        'workshop.render.pre',
        function(RenderExtension $extension) {
            foreach ($extension->products as $product) {
                $data[] = [
                    'id' => $product->getId(),
                    'active' => $product->getActive()
                ];
            }

            echo json_encode($data, JSON_PRETTY_PRINT);

            $extension->stopPropagation();
        }
);

$script->run();
