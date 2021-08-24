<?php

namespace App\Admin\Extensions\Form;

use Dcat\Admin\Support\Helper;
use Dcat\Admin\Form\Field\Tree;

class TreeSelect extends Tree
{
    protected $view = 'tree-select';

    protected $options = [
        'plugins' => ['checkbox', 'types'],
        'core'    => [
            'check_callback' => true,

            'themes' => [
                'name'       => 'proton',
                'responsive' => true,
                "ellipsis" => true, //节点名过长时是否显示省略号
            ],
            'dblclick_toggle' => false,   //允许tree的双击展开
        ],
        'checkbox' => [
            'keep_selected_style' => false,
            'three_state' => true,
            'cascade_to_disabled' => false,
            'whole_node' => false,
        ],
        'types' => [
            'default'  => [
                'icon' => false,
            ],
        ],
    ];

    protected function formatNodes()
    {
        $value = Helper::array($this->value());

        $this->value = &$value;

        if ($this->nodes instanceof \Closure) {
            $this->nodes = Helper::array($this->nodes->call($this->values(), $value, $this));
        }

        if (!$this->nodes) {
            return;
        }

        $idColumn = $this->columnNames['id'];
        $textColumn = $this->columnNames['text'];
        $parentColumn = $this->columnNames['parent'];

        $parentIds = $nodes = [];

        foreach ($this->nodes as &$v) {
            if (empty($v[$idColumn])) {
                continue;
            }

            $parentId = $v[$parentColumn] ?? '#';
            if (empty($parentId) || $parentId == $this->rootParentId) {
                $parentId = '#';
            } else {
                if (!isset($parentIds[$parentId])) {
                    $parentIds[$parentId] = true;
                }
                if (empty($v['state']['disabled'])) {
                    $parentIds[$parentId] = false;
                }
            }

            $v['state'] = $v['state'] ?? [];

            if ($value && in_array($v[$idColumn], $value)) {
                $v['state']['selected'] = true;
            }

            if ($this->readOnly) {
                $v['state']['disabled'] = true;
            }

            $nodes[] = [
                'id'     => $v[$idColumn],
                'text'   => $v[$textColumn] ?? null,
                'parent' => $parentId,
                'state'  => $v['state'],
            ];
        }

        foreach ($nodes as $key => $node) {
            if (isset($parentIds[$node['id']])) {
                $nodes[$key]['state']['disabled'] = $parentIds[$node['id']];
            }
        }

        if ($this->exceptParents) {
            // 筛选出所有父节点，最终点击树节点时过滤掉父节点
            $this->parents = array_keys($parentIds);
        }

        $this->nodes = &$nodes;
    }
}