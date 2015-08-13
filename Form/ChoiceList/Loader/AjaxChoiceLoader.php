<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Bundle\FormExtensionsBundle\Form\ChoiceList\Loader;

use Sonatra\Bundle\FormExtensionsBundle\Form\ChoiceList\Loader\Traits\AjaxLoaderTrait;
use Symfony\Component\Form\ChoiceList\Factory\ChoiceListFactoryInterface;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class AjaxChoiceLoader extends DynamicChoiceLoader implements AjaxChoiceLoaderInterface
{
    use AjaxLoaderTrait;

    /**
     * @var array
     */
    protected $filteredChoices;

    /**
     * Creates a new choice loader.
     *
     * @param array                           $choices        The choices
     * @param bool                            $choiceAsValues Check if the values are the keys in choices
     * @param ChoiceListFactoryInterface|null $factory        The factory for creating
     *                                                        the loaded choice list
     */
    public function __construct(array $choices, $choiceAsValues = false, $factory = null)
    {
        parent::__construct($choices, $choiceAsValues, $factory);

        $this->allChoices = false;
        $this->initAjax();
        $this->reset();
    }

    /**
     * {@inheritdoc}
     */
    public function loadPaginatedChoiceList($value = null)
    {
        $choices = LoaderUtil::paginateChoices($this, $this->filteredChoices);

        return $this->createChoiceList($choices, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function reset()
    {
        if (null === $this->search || '' === $this->search) {
            $filteredChoices = $this->choices;
        } else {
            $filteredChoices = $this->resetSearchChoices();
        }

        $this->initialize($filteredChoices);

        return $this;
    }

    /**
     * Reset the choices for search.
     *
     * @return array The filtered choices
     */
    protected function resetSearchChoices()
    {
        $filteredChoices = array();

        foreach ($this->choices as $group => $choice) {
            if (is_array($choice)) {
                foreach ($choice as $key => $subChoice) {
                    list($id, $label) = $this->getIdAndLabel($key, $subChoice);

                    if (false !== stripos($label, $this->search) && !in_array($id, $this->getIds())) {
                        if (!array_key_exists($group, $filteredChoices)) {
                            $filteredChoices[$group] = array();
                        }

                        $filteredChoices[$group][$key] = $subChoice;
                    }
                }
            } else {
                list($id, $label) = $this->getIdAndLabel($group, $choice);

                if (false !== stripos($label, $this->search) && !in_array($id, $this->getIds())) {
                    $filteredChoices[$group] = $choice;
                }
            }
        }

        return $filteredChoices;
    }

    /**
     * Get the id and label of original choices.
     *
     * @param string $key   The key of array
     * @param string $value The value of array
     *
     * @return array The id and label
     */
    protected function getIdAndLabel($key, $value)
    {
        return $this->choiceAsValues
            ? array($value, $key)
            : array($key, $value);
    }

    /**
     * @param array $choices The choices
     */
    protected function initialize($choices)
    {
        parent::initialize($choices);

        $this->filteredChoices = $choices;
        $this->choiceList = null;
    }

    /**
     * {@inheritdoc}
     */
    protected function getChoicesForChoiceList()
    {
        return $this->filteredChoices;
    }
}