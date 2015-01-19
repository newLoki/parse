<?php

namespace Psecio\Parse;

use ArrayIterator;
use DirectoryIterator;

/**
 * Responsible for creating a RuleCollection with the bundled set of rules
 */
class RuleFactory
{
    /**
     * @var RuleInterface[] Current ruleset
     */
    private $rules;

    /**
     * Register include and exclude rules
     *
     * @param string[] $include Names of rules to include in collection (no value means all rules)
     * @param string[] $exclude Names of rules to exclude from collection
     */
    public function __construct(array $include = array(), array $exclude = array())
    {
        $this->rules = $this->includeFilter(
            $this->excludeFilter(
                $this->getBundledRules(),
                $exclude
            ),
            $include
        );
    }

    /**
     * Create collection of rules
     *
     * @return RuleCollection
     */
    public function createRuleCollection()
    {
        return new RuleCollection($this->rules);
    }

    /**
     * Get rules included in source
     *
     * @return RuleInterface[]
     */
    private function getBundledRules()
    {
        $rules = [];

        foreach (new DirectoryIterator(__DIR__ . '/Rule') as $splFileInfo) {
            if ($splFileInfo->isDot() || $splFileInfo->isDir()) {
                continue;
            }
            $className = "\\Psecio\\Parse\\Rule\\{$splFileInfo->getBasename('.php')}";
            $rules[] = new $className;
        }

        return $rules;
    }

    /**
     * Apply include filter
     *
     * @param  RuleInterface[] $rules Current ruleset
     * @param  string[]        $include Names of rules to include in collection
     * @return RuleInterface[] Filtered ruleset
     */
    private function includeFilter(array $rules, array $include)
    {
        return array_filter(
            $rules,
            function (RuleInterface $rule) use ($include) {
                return empty($include) || in_array($rule->getName(), $include);
            }
        );
    }

    /**
     * Apply exclude filter
     *
     * @param  RuleInterface[] $rules Current ruleset
     * @param  string[]        $exclude Names of rules to exclude from collection
     * @return RuleInterface[] Filtered ruleset
     */
    private function excludeFilter(array $rules, array $exclude)
    {
        return array_filter(
            $rules,
            function (RuleInterface $rule) use ($exclude) {
                return !in_array($rule->getName(), $exclude);
            }
        );
    }
}
