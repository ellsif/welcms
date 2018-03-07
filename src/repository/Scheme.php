<?php


namespace ellsif\WelCMS;

use ellsif\util\StringUtil;

abstract class Scheme
{
    abstract public function getDefinition(): array;

    public function getName(): string
    {
        $class = get_class($this);
        return lcfirst(StringUtil::rightRemove(substr($class, strrpos($class, '\\') + 1), 'Scheme'));
    }

    /**
     * カラム名に対応するラベルを取得します。
     */
    public function getLabel(string $name): string
    {
        if (isset($this->getDefinition()[$name])) {
            return $this->getDefinition()[$name]['label'] ?? '';
        }
        return '';
    }

    /**
     * カラム名とラベルの対応リストを取得します。
     */
    public function getLabels(): array
    {
        $labels = [];
        foreach($this->getDefinition() as $name => $data) {
            $labels[$name] = $data['label'] ?? '';
        }
        return $labels;
    }

    /**
     * 選択肢の対応リストを取得します。
     */
    public function getSelectItems(string $name): array
    {
        if (isset($this->getDefinition()[$name])) {
            return $this->getDefinition()[$name]['items'] ?? [];
        }
        return [];
    }
}