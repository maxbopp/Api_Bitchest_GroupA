<?php

namespace GenerateCryptos;

/**
 * Renvoie la valeur de mise sur le marché de la crypto monnaie
 * @param $cryptoname {string} Le nom de la crypto monnaie
 */
function getFirstCotation($cryptoname){
  return ord(substr($cryptoname,0,1)) + rand(0, 10);
}

/**
 * Renvoie la variation de cotation de la crypto monnaie sur un jour
 * @param $cryptoname {string} Le nom de la crypto monnaie
 */
function getCotationFor($cryptoname){	
	return ((rand(0, 99)>40) ? 1 : -1) * ((rand(0, 99)>49) ? ord(substr($cryptoname,0,1)) : ord(substr($cryptoname,-1))) * (rand(1,10) * .01);
}

/**
 * génére les prix et les variations pour les cryptos
 * @param $cryptos {Array} le tableau des cryptos dont don doit générer le prix et les variations
 */
function GeneratedPriceAndVariationForCryptoOn30days(){
  $CrytoName = ['Bitcoin','Ethereum','Ripple','Bitcoin Cash','Cardano','Litecoin','NEM','Stellar','IOTA','Dash'];
  $CrytosPriceAndVariations = [];
  // On crée une classe pour les coins
  class Coins {
    public $name;
    public $price;
    public $Variations; 
  }
 
  // On boucle sur les cryptos
  foreach ($CrytoName as $crypto) {
    $Variations = [];
    // On instacie un nouveau coin
    $Coin = New Coins;
    // On donne le nom au bitcoins
    $Coin->name = $crypto;
    // On génére un prix 
    $Coin->price = getFirstCotation($crypto);
    // On va générer la cotation sur 30 jours
    for($i = 0; $i < 30; $i++){
      // On crée les variations pour la crypto
      $Variation = getCotationFor($crypto);
      $Variations[] = $Variation;
    }
    // On set les variations
    $Coin->Variations = $Variations;
    // On ajoute le bitCoins au tableaux
    $CrytosPriceAndVariations[$crypto] = $Coin;
  }
  // On retourne le tableau des cryptos avec leurs prix et leurs variations sur 30 jours
  return $CrytosPriceAndVariations;
}