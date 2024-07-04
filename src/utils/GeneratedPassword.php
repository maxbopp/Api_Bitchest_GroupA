<?php

namespace PasswordGenerator;

/**
 * Renvoie une mot de passe généré aléatoirement 
 */

function GeneratePassword(){
  //  chaine de caractére pour la générations du mot de passe
  $string = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789@#!^_-";
  // On mélange la chaine de caractére avec shuffle 
  $stringshuffle = str_shuffle($string);
  // On coupe la chaine de caractére 
  return $password = substr($stringshuffle,0,15);
};