<?php

class shopFacebookproCheckUse1C
{
	private $product1C = true;
	private $productsVariatons = null;

	public function __construct($product1C, $productsVariatons)
	{
		$this->product1C = $product1C;
		$this->productsVariatons = $productsVariatons;
	}

	public function checkUse1Cproduct(){
		 				$count1cequelvar = 0;
							foreach	($this->productsVariatons as $pkey => $pvalue) {
									$id_1cVar = $this->productsVariatons[$pkey]['id_1c'];
										if ($id_1cVar==$this->product1C['id_1c']) {
											$count1cequelvar++;
										}
							}
							if ($count1cequelvar == 1) {
								return true;
							} else {
								return false;
							}
	}

}