<?php
/**
 * @author Serge Rodovnichenko, <serge@syrnik.com>
 * @copyright Serge Rodovnichenko, 2018
 * @license
 */

namespace SergeR\Webasyst\Shipping\Packages;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\Expr\Comparison;
use PhpUnitsOfMeasure\PhysicalQuantity\Mass;

class PackageCollection extends ArrayCollection
{
    public function __construct(array $elements = array())
    {
        $elements = array_filter($elements, function ($e) {
            return $e instanceof Package;
        });
        parent::__construct($elements);
    }

    public function add($element)
    {
        if ($element instanceof Package) {
            return parent::add($element);
        }
        return false;
    }

    public function findPackageForWeight(Mass $weight)
    {
        $criteria = Criteria::create()
            ->where(new Comparison('native_min_weight', Comparison::LTE, $weight->toNativeUnit()))
            ->orderBy(['native_min_weight' => Criteria::ASC]);

        return $this->matching($criteria)->last();
    }

    /**
     * @param string|null $unit
     * @param int|null $decs
     * @param string|null $divider
     * @param string|null $dec_divider
     * @return array
     */
    public function toArrayOfSizes($unit = null, $decs = null, $divider = null, $dec_divider = null)
    {
        $criteria = Criteria::create()->orderBy(['native_min_weight' => Criteria::ASC]);
        $_c = $this->matching($criteria);

        $result = [];
        foreach ($_c as $_i) {
            /** @var Package $_i */
            $result[] = $_i->stringify($unit, $decs, $divider, $dec_divider);
        }

        return $result;
    }
}