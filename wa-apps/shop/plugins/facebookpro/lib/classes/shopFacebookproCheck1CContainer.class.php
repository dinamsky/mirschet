<?php

class shopFacebookproCheck1CContainer
{
	private $id1Cproduct = false; // задаем значение, чтобы при сравнении, если сбой было в любом случае отличие
	private $id1CVariationOfProduct = true; // задаем значение, чтобы при сравнении, если сбой было в любом случае отличие
	//protected $isItVariationOrContainer = true; // По умолчание, да, - вариация
	public function __construct($id1Cproduct, $id1CVariation)
	{
		$this->id1Cproduct = $id1Cproduct;
		$this->id1CVariationOfProduct = $id1CVariation;
	}
	public function isItVariation(){
		if ($this->id1Cproduct == $this->id1CVariationOfProduct) {
			return false; // Значит контейнер в 1С
		}
		else {
			return true;	// Значит вариация в 1С
		}
	}
}