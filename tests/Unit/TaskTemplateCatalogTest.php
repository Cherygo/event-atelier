<?php

namespace Tests\Unit;

use App\TaskTemplateCatalog;
use PHPUnit\Framework\TestCase;

class TaskTemplateCatalogTest extends TestCase
{
    public function test_templates_offer_consistent_editable_task_content_with_stable_shared_keys(): void
    {
        $catalog = new TaskTemplateCatalog;
        $templates = $catalog->all();
        $this->assertSame(['wedding', 'corporate', 'private'], array_column($templates, 'key'));
        $seen = [];
        foreach ($templates as $template) {
            $keys = array_column($template['items'], 'key');
            $this->assertSame($keys, array_values(array_unique($keys)));
            foreach ($template['items'] as $item) {
                $this->assertNotEmpty($item['title']);
                $this->assertLessThanOrEqual(180, strlen($item['title']));
                $this->assertLessThanOrEqual(60, strlen($item['category']));
                $this->assertGreaterThanOrEqual(0, $item['days_before']);
                if (isset($seen[$item['key']])) {
                    $this->assertSame($seen[$item['key']], $item);
                }
                $seen[$item['key']] = $item;
            }
        }
        $this->assertSame($templates[0], $catalog->find('wedding'));
        $this->assertNull($catalog->find('unknown'));
    }
}
