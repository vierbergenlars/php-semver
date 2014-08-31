<?php

namespace vierbergenlars\SemVer\Internal;

use vierbergenlars\SemVer\Internal\PartialVersion;
use vierbergenlars\SemVer\Internal\Expr;

class LooseSemVerParser extends SemVerParser
{
    protected function parseNum()
    {
        $_position = $this->position;
    
        if (isset($this->cache['Num'][$_position])) {
            $_success = $this->cache['Num'][$_position]['success'];
            $this->position = $this->cache['Num'][$_position]['position'];
            $this->value = $this->cache['Num'][$_position]['value'];
    
            return $_success;
        }
    
        if (preg_match('/^[0-9]$/', substr($this->string, $this->position, 1))) {
            $_success = true;
            $this->value = substr($this->string, $this->position, 1);
            $this->position += 1;
        } else {
            $_success = false;
        }
        
        if ($_success) {
            $_value2 = array($this->value);
            $_cut3 = $this->cut;
        
            while (true) {
                $_position1 = $this->position;
        
                $this->cut = false;
                if (preg_match('/^[0-9]$/', substr($this->string, $this->position, 1))) {
                    $_success = true;
                    $this->value = substr($this->string, $this->position, 1);
                    $this->position += 1;
                } else {
                    $_success = false;
                }
        
                if (!$_success) {
                    break;
                }
        
                $_value2[] = $this->value;
            }
        
            if (!$this->cut) {
                $_success = true;
                $this->position = $_position1;
                $this->value = $_value2;
            }
        
            $this->cut = $_cut3;
        }
        
        if ($_success) {
            $n = $this->value;
        }
        
        if ($_success) {
            $this->value = call_user_func(function () use (&$n) {
                return implode('', $n);
            });
        }
    
        $this->cache['Num'][$_position] = array(
            'success' => $_success,
            'position' => $this->position,
            'value' => $this->value
        );
    
        if (!$_success) {
            $this->report($_position, 'Num');
        }
    
        return $_success;
    }
}