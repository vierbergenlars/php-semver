<?php

namespace vierbergenlars\SemVer\Internal;

use vierbergenlars\SemVer\Internal\PartialVersion;
use vierbergenlars\SemVer\Internal\Expr;

class SemVerParser
{
    protected $string;
    protected $position;
    protected $value;
    protected $cache;
    protected $cut;
    protected $errors;
    protected $warnings;

    protected function parseOrExpr()
    {
        $_position = $this->position;
    
        if (isset($this->cache['OrExpr'][$_position])) {
            $_success = $this->cache['OrExpr'][$_position]['success'];
            $this->position = $this->cache['OrExpr'][$_position]['position'];
            $this->value = $this->cache['OrExpr'][$_position]['value'];
    
            return $_success;
        }
    
        $_value5 = array();
        
        $_success = $this->parseAndExpr();
        
        if ($_success) {
            $head = $this->value;
        }
        
        if ($_success) {
            $_value5[] = $this->value;
        
            $_value3 = array();
            $_cut4 = $this->cut;
            
            while (true) {
                $_position2 = $this->position;
            
                $this->cut = false;
                $_value1 = array();
                
                $_success = $this->parse_();
                
                if ($_success) {
                    $_value1[] = $this->value;
                
                    if (substr($this->string, $this->position, strlen("||")) === "||") {
                        $_success = true;
                        $this->value = substr($this->string, $this->position, strlen("||"));
                        $this->position += strlen("||");
                    } else {
                        $_success = false;
                    
                        $this->report($this->position, '"||"');
                    }
                }
                
                if ($_success) {
                    $_value1[] = $this->value;
                
                    $_success = $this->parse_();
                }
                
                if ($_success) {
                    $_value1[] = $this->value;
                
                    $_success = $this->parseAndExpr();
                    
                    if ($_success) {
                        $r = $this->value;
                    }
                }
                
                if ($_success) {
                    $_value1[] = $this->value;
                
                    $this->value = $_value1;
                }
                
                if ($_success) {
                    $this->value = call_user_func(function () use (&$head, &$r) {
                        return $r;
                    });
                }
            
                if (!$_success) {
                    break;
                }
            
                $_value3[] = $this->value;
            }
            
            if (!$this->cut) {
                $_success = true;
                $this->position = $_position2;
                $this->value = $_value3;
            }
            
            $this->cut = $_cut4;
            
            if ($_success) {
                $tail = $this->value;
            }
        }
        
        if ($_success) {
            $_value5[] = $this->value;
        
            $this->value = $_value5;
        }
        
        if ($_success) {
            $this->value = call_user_func(function () use (&$head, &$r, &$tail) {
                return new Expr\OrExpression(array_merge(array($head), $tail));
            });
        }
    
        $this->cache['OrExpr'][$_position] = array(
            'success' => $_success,
            'position' => $this->position,
            'value' => $this->value
        );
    
        if (!$_success) {
            $this->report($_position, 'OrExpr');
        }
    
        return $_success;
    }

    protected function parseAndExpr()
    {
        $_position = $this->position;
    
        if (isset($this->cache['AndExpr'][$_position])) {
            $_success = $this->cache['AndExpr'][$_position]['success'];
            $this->position = $this->cache['AndExpr'][$_position]['position'];
            $this->value = $this->cache['AndExpr'][$_position]['value'];
    
            return $_success;
        }
    
        $_value10 = array();
        
        $_success = $this->parseRange();
        
        if ($_success) {
            $head = $this->value;
        }
        
        if ($_success) {
            $_value10[] = $this->value;
        
            $_value8 = array();
            $_cut9 = $this->cut;
            
            while (true) {
                $_position7 = $this->position;
            
                $this->cut = false;
                $_value6 = array();
                
                $_success = $this->parse__();
                
                if ($_success) {
                    $_value6[] = $this->value;
                
                    $_success = $this->parseRange();
                    
                    if ($_success) {
                        $r = $this->value;
                    }
                }
                
                if ($_success) {
                    $_value6[] = $this->value;
                
                    $this->value = $_value6;
                }
                
                if ($_success) {
                    $this->value = call_user_func(function () use (&$head, &$r) {
                        return $r;
                    });
                }
            
                if (!$_success) {
                    break;
                }
            
                $_value8[] = $this->value;
            }
            
            if (!$this->cut) {
                $_success = true;
                $this->position = $_position7;
                $this->value = $_value8;
            }
            
            $this->cut = $_cut9;
            
            if ($_success) {
                $tail = $this->value;
            }
        }
        
        if ($_success) {
            $_value10[] = $this->value;
        
            $this->value = $_value10;
        }
        
        if ($_success) {
            $this->value = call_user_func(function () use (&$head, &$r, &$tail) {
                return new Expr\AndExpression(array_merge(array($head), $tail));
            });
        }
    
        $this->cache['AndExpr'][$_position] = array(
            'success' => $_success,
            'position' => $this->position,
            'value' => $this->value
        );
    
        if (!$_success) {
            $this->report($_position, 'AndExpr');
        }
    
        return $_success;
    }

    protected function parseRange()
    {
        $_position = $this->position;
    
        if (isset($this->cache['Range'][$_position])) {
            $_success = $this->cache['Range'][$_position]['success'];
            $this->position = $this->cache['Range'][$_position]['position'];
            $this->value = $this->cache['Range'][$_position]['value'];
    
            return $_success;
        }
    
        $_position13 = $this->position;
        $_cut14 = $this->cut;
        
        $this->cut = false;
        $_success = $this->parseGtLtVersion();
        
        if (!$_success && !$this->cut) {
            $this->position = $_position13;
        
            $_value11 = array();
            
            $_success = $this->parseVersion();
            
            if ($_success) {
                $vl = $this->value;
            }
            
            if ($_success) {
                $_value11[] = $this->value;
            
                $_success = $this->parse__();
            }
            
            if ($_success) {
                $_value11[] = $this->value;
            
                if (substr($this->string, $this->position, strlen("-")) === "-") {
                    $_success = true;
                    $this->value = substr($this->string, $this->position, strlen("-"));
                    $this->position += strlen("-");
                } else {
                    $_success = false;
                
                    $this->report($this->position, '"-"');
                }
            }
            
            if ($_success) {
                $_value11[] = $this->value;
            
                $_success = $this->parse__();
            }
            
            if ($_success) {
                $_value11[] = $this->value;
            
                $_success = $this->parseVersion();
                
                if ($_success) {
                    $vr = $this->value;
                }
            }
            
            if ($_success) {
                $_value11[] = $this->value;
            
                $this->value = $_value11;
            }
            
            if ($_success) {
                $this->value = call_user_func(function () use (&$vl, &$vr) {
                    return new Expr\RangeExpression($vl, $vr);
                });
            }
        }
        
        if (!$_success && !$this->cut) {
            $this->position = $_position13;
        
            $_value12 = array();
            
            if (substr($this->string, $this->position, strlen("(")) === "(") {
                $_success = true;
                $this->value = substr($this->string, $this->position, strlen("("));
                $this->position += strlen("(");
            } else {
                $_success = false;
            
                $this->report($this->position, '"("');
            }
            
            if ($_success) {
                $_value12[] = $this->value;
            
                $_success = true;
                $this->value = null;
                
                $this->cut = true;
            }
            
            if ($_success) {
                $_value12[] = $this->value;
            
                $_success = $this->parse_();
            }
            
            if ($_success) {
                $_value12[] = $this->value;
            
                $_success = $this->parseOrExpr();
                
                if ($_success) {
                    $expr = $this->value;
                }
            }
            
            if ($_success) {
                $_value12[] = $this->value;
            
                $_success = $this->parse_();
            }
            
            if ($_success) {
                $_value12[] = $this->value;
            
                if (substr($this->string, $this->position, strlen(")")) === ")") {
                    $_success = true;
                    $this->value = substr($this->string, $this->position, strlen(")"));
                    $this->position += strlen(")");
                } else {
                    $_success = false;
                
                    $this->report($this->position, '")"');
                }
            }
            
            if ($_success) {
                $_value12[] = $this->value;
            
                $this->value = $_value12;
            }
            
            if ($_success) {
                $this->value = call_user_func(function () use (&$vl, &$vr, &$expr) {
                    return $expr;
                });
            }
        }
        
        if (!$_success && !$this->cut) {
            $this->position = $_position13;
        
            $_success = $this->parseVersion();
        }
        
        $this->cut = $_cut14;
    
        $this->cache['Range'][$_position] = array(
            'success' => $_success,
            'position' => $this->position,
            'value' => $this->value
        );
    
        if (!$_success) {
            $this->report($_position, 'Range');
        }
    
        return $_success;
    }

    protected function parseGtLtVersion()
    {
        $_position = $this->position;
    
        if (isset($this->cache['GtLtVersion'][$_position])) {
            $_success = $this->cache['GtLtVersion'][$_position]['success'];
            $this->position = $this->cache['GtLtVersion'][$_position]['position'];
            $this->value = $this->cache['GtLtVersion'][$_position]['value'];
    
            return $_success;
        }
    
        $_position21 = $this->position;
        $_cut22 = $this->cut;
        
        $this->cut = false;
        $_value15 = array();
        
        if (substr($this->string, $this->position, strlen(">")) === ">") {
            $_success = true;
            $this->value = substr($this->string, $this->position, strlen(">"));
            $this->position += strlen(">");
        } else {
            $_success = false;
        
            $this->report($this->position, '">"');
        }
        
        if ($_success) {
            $_value15[] = $this->value;
        
            $_success = $this->parse_();
        }
        
        if ($_success) {
            $_value15[] = $this->value;
        
            $_success = $this->parseVersion();
            
            if ($_success) {
                $v = $this->value;
            }
        }
        
        if ($_success) {
            $_value15[] = $this->value;
        
            $this->value = $_value15;
        }
        
        if ($_success) {
            $this->value = call_user_func(function () use (&$v) {
                return new Expr\GreaterThanExpression($v);
            });
        }
        
        if (!$_success && !$this->cut) {
            $this->position = $_position21;
        
            $_value16 = array();
            
            if (substr($this->string, $this->position, strlen(">=")) === ">=") {
                $_success = true;
                $this->value = substr($this->string, $this->position, strlen(">="));
                $this->position += strlen(">=");
            } else {
                $_success = false;
            
                $this->report($this->position, '">="');
            }
            
            if ($_success) {
                $_value16[] = $this->value;
            
                $_success = $this->parse_();
            }
            
            if ($_success) {
                $_value16[] = $this->value;
            
                $_success = $this->parseVersion();
                
                if ($_success) {
                    $v = $this->value;
                }
            }
            
            if ($_success) {
                $_value16[] = $this->value;
            
                $this->value = $_value16;
            }
            
            if ($_success) {
                $this->value = call_user_func(function () use (&$v, &$v) {
                    return new Expr\GreaterThanOrEqualExpression($v);
                });
            }
        }
        
        if (!$_success && !$this->cut) {
            $this->position = $_position21;
        
            $_value17 = array();
            
            if (substr($this->string, $this->position, strlen("<=")) === "<=") {
                $_success = true;
                $this->value = substr($this->string, $this->position, strlen("<="));
                $this->position += strlen("<=");
            } else {
                $_success = false;
            
                $this->report($this->position, '"<="');
            }
            
            if ($_success) {
                $_value17[] = $this->value;
            
                $_success = $this->parse_();
            }
            
            if ($_success) {
                $_value17[] = $this->value;
            
                $_success = $this->parseVersion();
                
                if ($_success) {
                    $v = $this->value;
                }
            }
            
            if ($_success) {
                $_value17[] = $this->value;
            
                $this->value = $_value17;
            }
            
            if ($_success) {
                $this->value = call_user_func(function () use (&$v, &$v, &$v) {
                    return new Expr\LessThanOrEqualExpression($v);
                });
            }
        }
        
        if (!$_success && !$this->cut) {
            $this->position = $_position21;
        
            $_value18 = array();
            
            if (substr($this->string, $this->position, strlen("<")) === "<") {
                $_success = true;
                $this->value = substr($this->string, $this->position, strlen("<"));
                $this->position += strlen("<");
            } else {
                $_success = false;
            
                $this->report($this->position, '"<"');
            }
            
            if ($_success) {
                $_value18[] = $this->value;
            
                $_success = $this->parse_();
            }
            
            if ($_success) {
                $_value18[] = $this->value;
            
                $_success = $this->parseVersion();
                
                if ($_success) {
                    $v = $this->value;
                }
            }
            
            if ($_success) {
                $_value18[] = $this->value;
            
                $this->value = $_value18;
            }
            
            if ($_success) {
                $this->value = call_user_func(function () use (&$v, &$v, &$v, &$v) {
                    return new Expr\LessThanExpression($v);
                });
            }
        }
        
        if (!$_success && !$this->cut) {
            $this->position = $_position21;
        
            $_value19 = array();
            
            if (substr($this->string, $this->position, strlen("~")) === "~") {
                $_success = true;
                $this->value = substr($this->string, $this->position, strlen("~"));
                $this->position += strlen("~");
            } else {
                $_success = false;
            
                $this->report($this->position, '"~"');
            }
            
            if ($_success) {
                $_value19[] = $this->value;
            
                $_success = $this->parse_();
            }
            
            if ($_success) {
                $_value19[] = $this->value;
            
                $_success = $this->parseVersion();
                
                if ($_success) {
                    $v = $this->value;
                }
            }
            
            if ($_success) {
                $_value19[] = $this->value;
            
                $this->value = $_value19;
            }
            
            if ($_success) {
                $this->value = call_user_func(function () use (&$v, &$v, &$v, &$v, &$v) {
                    return new Expr\SquiggleExpression($v);
                });
            }
        }
        
        if (!$_success && !$this->cut) {
            $this->position = $_position21;
        
            $_value20 = array();
            
            if (substr($this->string, $this->position, strlen("^")) === "^") {
                $_success = true;
                $this->value = substr($this->string, $this->position, strlen("^"));
                $this->position += strlen("^");
            } else {
                $_success = false;
            
                $this->report($this->position, '"^"');
            }
            
            if ($_success) {
                $_value20[] = $this->value;
            
                $_success = $this->parse_();
            }
            
            if ($_success) {
                $_value20[] = $this->value;
            
                $_success = $this->parseVersion();
                
                if ($_success) {
                    $v = $this->value;
                }
            }
            
            if ($_success) {
                $_value20[] = $this->value;
            
                $this->value = $_value20;
            }
            
            if ($_success) {
                $this->value = call_user_func(function () use (&$v, &$v, &$v, &$v, &$v, &$v) {
                    return new Expr\CaretExpression($v);
                });
            }
        }
        
        $this->cut = $_cut22;
    
        $this->cache['GtLtVersion'][$_position] = array(
            'success' => $_success,
            'position' => $this->position,
            'value' => $this->value
        );
    
        if (!$_success) {
            $this->report($_position, 'GtLtVersion');
        }
    
        return $_success;
    }

    protected function parseVersion()
    {
        $_position = $this->position;
    
        if (isset($this->cache['Version'][$_position])) {
            $_success = $this->cache['Version'][$_position]['success'];
            $this->position = $this->cache['Version'][$_position]['position'];
            $this->value = $this->cache['Version'][$_position]['value'];
    
            return $_success;
        }
    
        $_value33 = array();
        
        $_success = $this->parseVersionNum();
        
        if ($_success) {
            $maj = $this->value;
        }
        
        if ($_success) {
            $_value33[] = $this->value;
        
            $_position31 = $this->position;
            $_cut32 = $this->cut;
            
            $this->cut = false;
            $_value30 = array();
            
            if (substr($this->string, $this->position, strlen(".")) === ".") {
                $_success = true;
                $this->value = substr($this->string, $this->position, strlen("."));
                $this->position += strlen(".");
            } else {
                $_success = false;
            
                $this->report($this->position, '"."');
            }
            
            if ($_success) {
                $_value30[] = $this->value;
            
                $_success = $this->parseVersionNum();
                
                if ($_success) {
                    $min = $this->value;
                }
            }
            
            if ($_success) {
                $_value30[] = $this->value;
            
                $_position28 = $this->position;
                $_cut29 = $this->cut;
                
                $this->cut = false;
                $_value27 = array();
                
                if (substr($this->string, $this->position, strlen(".")) === ".") {
                    $_success = true;
                    $this->value = substr($this->string, $this->position, strlen("."));
                    $this->position += strlen(".");
                } else {
                    $_success = false;
                
                    $this->report($this->position, '"."');
                }
                
                if ($_success) {
                    $_value27[] = $this->value;
                
                    $_success = $this->parseVersionNum();
                    
                    if ($_success) {
                        $pat = $this->value;
                    }
                }
                
                if ($_success) {
                    $_value27[] = $this->value;
                
                    $_position23 = $this->position;
                    $_cut24 = $this->cut;
                    
                    $this->cut = false;
                    $_success = $this->parsePreRel();
                    
                    if (!$_success && !$this->cut) {
                        $_success = true;
                        $this->position = $_position23;
                        $this->value = null;
                    }
                    
                    $this->cut = $_cut24;
                    
                    if ($_success) {
                        $pr = $this->value;
                    }
                }
                
                if ($_success) {
                    $_value27[] = $this->value;
                
                    $_position25 = $this->position;
                    $_cut26 = $this->cut;
                    
                    $this->cut = false;
                    $_success = $this->parseBuild();
                    
                    if (!$_success && !$this->cut) {
                        $_success = true;
                        $this->position = $_position25;
                        $this->value = null;
                    }
                    
                    $this->cut = $_cut26;
                    
                    if ($_success) {
                        $b = $this->value;
                    }
                }
                
                if ($_success) {
                    $_value27[] = $this->value;
                
                    $this->value = $_value27;
                }
                
                if (!$_success && !$this->cut) {
                    $_success = true;
                    $this->position = $_position28;
                    $this->value = null;
                }
                
                $this->cut = $_cut29;
            }
            
            if ($_success) {
                $_value30[] = $this->value;
            
                $this->value = $_value30;
            }
            
            if (!$_success && !$this->cut) {
                $_success = true;
                $this->position = $_position31;
                $this->value = null;
            }
            
            $this->cut = $_cut32;
        }
        
        if ($_success) {
            $_value33[] = $this->value;
        
            $this->value = $_value33;
        }
        
        if ($_success) {
            $this->value = call_user_func(function () use (&$maj, &$min, &$pat, &$pr, &$b) {
                return ($maj==='*'||$min==='*'||$pat==='*')
                        ?new Expr\XRangeExpression($maj, $min, $pat)
                        :new PartialVersion($maj, $min, $pat, $pr?:array(), $b?:array());
            });
        }
    
        $this->cache['Version'][$_position] = array(
            'success' => $_success,
            'position' => $this->position,
            'value' => $this->value
        );
    
        if (!$_success) {
            $this->report($_position, 'Version');
        }
    
        return $_success;
    }

    protected function parseVersionNum()
    {
        $_position = $this->position;
    
        if (isset($this->cache['VersionNum'][$_position])) {
            $_success = $this->cache['VersionNum'][$_position]['success'];
            $this->position = $this->cache['VersionNum'][$_position]['position'];
            $this->value = $this->cache['VersionNum'][$_position]['value'];
    
            return $_success;
        }
    
        $_position34 = $this->position;
        $_cut35 = $this->cut;
        
        $this->cut = false;
        $_success = $this->parseNum();
        
        if (!$_success && !$this->cut) {
            $this->position = $_position34;
        
            if (preg_match('/^[xX*]$/', substr($this->string, $this->position, 1))) {
                $_success = true;
                $this->value = substr($this->string, $this->position, 1);
                $this->position += 1;
            } else {
                $_success = false;
            }
            
            if ($_success) {
                $this->value = call_user_func(function () {
                    return '*';
                });
            }
        }
        
        $this->cut = $_cut35;
    
        $this->cache['VersionNum'][$_position] = array(
            'success' => $_success,
            'position' => $this->position,
            'value' => $this->value
        );
    
        if (!$_success) {
            $this->report($_position, 'VersionNum');
        }
    
        return $_success;
    }

    protected function parseNum()
    {
        $_position = $this->position;
    
        if (isset($this->cache['Num'][$_position])) {
            $_success = $this->cache['Num'][$_position]['success'];
            $this->position = $this->cache['Num'][$_position]['position'];
            $this->value = $this->cache['Num'][$_position]['value'];
    
            return $_success;
        }
    
        $_position39 = $this->position;
        $_cut40 = $this->cut;
        
        $this->cut = false;
        if (substr($this->string, $this->position, strlen("0")) === "0") {
            $_success = true;
            $this->value = substr($this->string, $this->position, strlen("0"));
            $this->position += strlen("0");
        } else {
            $_success = false;
        
            $this->report($this->position, '"0"');
        }
        
        if (!$_success && !$this->cut) {
            $this->position = $_position39;
        
            if (preg_match('/^[1-9]$/', substr($this->string, $this->position, 1))) {
                $_success = true;
                $this->value = substr($this->string, $this->position, 1);
                $this->position += 1;
            } else {
                $_success = false;
            }
            
            if ($_success) {
                $_value37 = array($this->value);
                $_cut38 = $this->cut;
            
                while (true) {
                    $_position36 = $this->position;
            
                    $this->cut = false;
                    if (preg_match('/^[1-9]$/', substr($this->string, $this->position, 1))) {
                        $_success = true;
                        $this->value = substr($this->string, $this->position, 1);
                        $this->position += 1;
                    } else {
                        $_success = false;
                    }
            
                    if (!$_success) {
                        break;
                    }
            
                    $_value37[] = $this->value;
                }
            
                if (!$this->cut) {
                    $_success = true;
                    $this->position = $_position36;
                    $this->value = $_value37;
                }
            
                $this->cut = $_cut38;
            }
            
            if ($_success) {
                $n = $this->value;
            }
            
            if ($_success) {
                $this->value = call_user_func(function () use (&$n) {
                    return implode('', $n);
                });
            }
        }
        
        $this->cut = $_cut40;
    
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

    protected function parsePreRel()
    {
        $_position = $this->position;
    
        if (isset($this->cache['PreRel'][$_position])) {
            $_success = $this->cache['PreRel'][$_position]['success'];
            $this->position = $this->cache['PreRel'][$_position]['position'];
            $this->value = $this->cache['PreRel'][$_position]['value'];
    
            return $_success;
        }
    
        $_value45 = array();
        
        if (substr($this->string, $this->position, strlen("-")) === "-") {
            $_success = true;
            $this->value = substr($this->string, $this->position, strlen("-"));
            $this->position += strlen("-");
        } else {
            $_success = false;
        
            $this->report($this->position, '"-"');
        }
        
        if ($_success) {
            $_value45[] = $this->value;
        
            $_success = $this->parsePreRelId();
            
            if ($_success) {
                $head = $this->value;
            }
        }
        
        if ($_success) {
            $_value45[] = $this->value;
        
            $_value43 = array();
            $_cut44 = $this->cut;
            
            while (true) {
                $_position42 = $this->position;
            
                $this->cut = false;
                $_value41 = array();
                
                if (substr($this->string, $this->position, strlen(".")) === ".") {
                    $_success = true;
                    $this->value = substr($this->string, $this->position, strlen("."));
                    $this->position += strlen(".");
                } else {
                    $_success = false;
                
                    $this->report($this->position, '"."');
                }
                
                if ($_success) {
                    $_value41[] = $this->value;
                
                    $_success = $this->parsePreRelId();
                    
                    if ($_success) {
                        $n = $this->value;
                    }
                }
                
                if ($_success) {
                    $_value41[] = $this->value;
                
                    $this->value = $_value41;
                }
                
                if ($_success) {
                    $this->value = call_user_func(function () use (&$head, &$n) {
                        return $n;
                    });
                }
            
                if (!$_success) {
                    break;
                }
            
                $_value43[] = $this->value;
            }
            
            if (!$this->cut) {
                $_success = true;
                $this->position = $_position42;
                $this->value = $_value43;
            }
            
            $this->cut = $_cut44;
            
            if ($_success) {
                $tail = $this->value;
            }
        }
        
        if ($_success) {
            $_value45[] = $this->value;
        
            $this->value = $_value45;
        }
        
        if ($_success) {
            $this->value = call_user_func(function () use (&$head, &$n, &$tail) {
                return array_merge(array($head), $tail);
            });
        }
    
        $this->cache['PreRel'][$_position] = array(
            'success' => $_success,
            'position' => $this->position,
            'value' => $this->value
        );
    
        if (!$_success) {
            $this->report($_position, 'PreRel');
        }
    
        return $_success;
    }

    protected function parsePreRelId()
    {
        $_position = $this->position;
    
        if (isset($this->cache['PreRelId'][$_position])) {
            $_success = $this->cache['PreRelId'][$_position]['success'];
            $this->position = $this->cache['PreRelId'][$_position]['position'];
            $this->value = $this->cache['PreRelId'][$_position]['value'];
    
            return $_success;
        }
    
        $_position50 = $this->position;
        $_cut51 = $this->cut;
        
        $this->cut = false;
        $_success = $this->parseNum();
        
        if ($_success) {
            $n = $this->value;
        }
        
        if ($_success) {
            $this->value = call_user_func(function () use (&$n) {
                return $n;
            });
        }
        
        if (!$_success && !$this->cut) {
            $this->position = $_position50;
        
            $_value49 = array();
            
            if (preg_match('/^[a-zA-Z-]$/', substr($this->string, $this->position, 1))) {
                $_success = true;
                $this->value = substr($this->string, $this->position, 1);
                $this->position += 1;
            } else {
                $_success = false;
            }
            
            if ($_success) {
                $head = $this->value;
            }
            
            if ($_success) {
                $_value49[] = $this->value;
            
                $_value47 = array();
                $_cut48 = $this->cut;
                
                while (true) {
                    $_position46 = $this->position;
                
                    $this->cut = false;
                    if (preg_match('/^[a-zA-Z0-9-]$/', substr($this->string, $this->position, 1))) {
                        $_success = true;
                        $this->value = substr($this->string, $this->position, 1);
                        $this->position += 1;
                    } else {
                        $_success = false;
                    }
                
                    if (!$_success) {
                        break;
                    }
                
                    $_value47[] = $this->value;
                }
                
                if (!$this->cut) {
                    $_success = true;
                    $this->position = $_position46;
                    $this->value = $_value47;
                }
                
                $this->cut = $_cut48;
                
                if ($_success) {
                    $tail = $this->value;
                }
            }
            
            if ($_success) {
                $_value49[] = $this->value;
            
                $this->value = $_value49;
            }
            
            if ($_success) {
                $this->value = call_user_func(function () use (&$n, &$head, &$tail) {
                    return $head.implode('', $tail);
                });
            }
        }
        
        $this->cut = $_cut51;
    
        $this->cache['PreRelId'][$_position] = array(
            'success' => $_success,
            'position' => $this->position,
            'value' => $this->value
        );
    
        if (!$_success) {
            $this->report($_position, 'PreRelId');
        }
    
        return $_success;
    }

    protected function parseBuild()
    {
        $_position = $this->position;
    
        if (isset($this->cache['Build'][$_position])) {
            $_success = $this->cache['Build'][$_position]['success'];
            $this->position = $this->cache['Build'][$_position]['position'];
            $this->value = $this->cache['Build'][$_position]['value'];
    
            return $_success;
        }
    
        $_value56 = array();
        
        if (substr($this->string, $this->position, strlen("+")) === "+") {
            $_success = true;
            $this->value = substr($this->string, $this->position, strlen("+"));
            $this->position += strlen("+");
        } else {
            $_success = false;
        
            $this->report($this->position, '"+"');
        }
        
        if ($_success) {
            $_value56[] = $this->value;
        
            $_success = $this->parseBuildId();
            
            if ($_success) {
                $head = $this->value;
            }
        }
        
        if ($_success) {
            $_value56[] = $this->value;
        
            $_value54 = array();
            $_cut55 = $this->cut;
            
            while (true) {
                $_position53 = $this->position;
            
                $this->cut = false;
                $_value52 = array();
                
                if (substr($this->string, $this->position, strlen(".")) === ".") {
                    $_success = true;
                    $this->value = substr($this->string, $this->position, strlen("."));
                    $this->position += strlen(".");
                } else {
                    $_success = false;
                
                    $this->report($this->position, '"."');
                }
                
                if ($_success) {
                    $_value52[] = $this->value;
                
                    $_success = $this->parseBuildId();
                    
                    if ($_success) {
                        $n = $this->value;
                    }
                }
                
                if ($_success) {
                    $_value52[] = $this->value;
                
                    $this->value = $_value52;
                }
                
                if ($_success) {
                    $this->value = call_user_func(function () use (&$head, &$n) {
                        return $n;
                    });
                }
            
                if (!$_success) {
                    break;
                }
            
                $_value54[] = $this->value;
            }
            
            if (!$this->cut) {
                $_success = true;
                $this->position = $_position53;
                $this->value = $_value54;
            }
            
            $this->cut = $_cut55;
            
            if ($_success) {
                $tail = $this->value;
            }
        }
        
        if ($_success) {
            $_value56[] = $this->value;
        
            $this->value = $_value56;
        }
        
        if ($_success) {
            $this->value = call_user_func(function () use (&$head, &$n, &$tail) {
                return array_merge(array($head), $tail);
            });
        }
    
        $this->cache['Build'][$_position] = array(
            'success' => $_success,
            'position' => $this->position,
            'value' => $this->value
        );
    
        if (!$_success) {
            $this->report($_position, 'Build');
        }
    
        return $_success;
    }

    protected function parseBuildId()
    {
        $_position = $this->position;
    
        if (isset($this->cache['BuildId'][$_position])) {
            $_success = $this->cache['BuildId'][$_position]['success'];
            $this->position = $this->cache['BuildId'][$_position]['position'];
            $this->value = $this->cache['BuildId'][$_position]['value'];
    
            return $_success;
        }
    
        if (preg_match('/^[0-9A-Za-z-]$/', substr($this->string, $this->position, 1))) {
            $_success = true;
            $this->value = substr($this->string, $this->position, 1);
            $this->position += 1;
        } else {
            $_success = false;
        }
        
        if ($_success) {
            $_value58 = array($this->value);
            $_cut59 = $this->cut;
        
            while (true) {
                $_position57 = $this->position;
        
                $this->cut = false;
                if (preg_match('/^[0-9A-Za-z-]$/', substr($this->string, $this->position, 1))) {
                    $_success = true;
                    $this->value = substr($this->string, $this->position, 1);
                    $this->position += 1;
                } else {
                    $_success = false;
                }
        
                if (!$_success) {
                    break;
                }
        
                $_value58[] = $this->value;
            }
        
            if (!$this->cut) {
                $_success = true;
                $this->position = $_position57;
                $this->value = $_value58;
            }
        
            $this->cut = $_cut59;
        }
        
        if ($_success) {
            $n = $this->value;
        }
        
        if ($_success) {
            $this->value = call_user_func(function () use (&$n) {
                return implode('', $n);
            });
        }
    
        $this->cache['BuildId'][$_position] = array(
            'success' => $_success,
            'position' => $this->position,
            'value' => $this->value
        );
    
        if (!$_success) {
            $this->report($_position, 'BuildId');
        }
    
        return $_success;
    }

    protected function parse_()
    {
        $_position = $this->position;
    
        if (isset($this->cache['_'][$_position])) {
            $_success = $this->cache['_'][$_position]['success'];
            $this->position = $this->cache['_'][$_position]['position'];
            $this->value = $this->cache['_'][$_position]['value'];
    
            return $_success;
        }
    
        $_value61 = array();
        $_cut62 = $this->cut;
        
        while (true) {
            $_position60 = $this->position;
        
            $this->cut = false;
            if (substr($this->string, $this->position, strlen(" ")) === " ") {
                $_success = true;
                $this->value = substr($this->string, $this->position, strlen(" "));
                $this->position += strlen(" ");
            } else {
                $_success = false;
            
                $this->report($this->position, '" "');
            }
        
            if (!$_success) {
                break;
            }
        
            $_value61[] = $this->value;
        }
        
        if (!$this->cut) {
            $_success = true;
            $this->position = $_position60;
            $this->value = $_value61;
        }
        
        $this->cut = $_cut62;
    
        $this->cache['_'][$_position] = array(
            'success' => $_success,
            'position' => $this->position,
            'value' => $this->value
        );
    
        if (!$_success) {
            $this->report($_position, '_');
        }
    
        return $_success;
    }

    protected function parse__()
    {
        $_position = $this->position;
    
        if (isset($this->cache['__'][$_position])) {
            $_success = $this->cache['__'][$_position]['success'];
            $this->position = $this->cache['__'][$_position]['position'];
            $this->value = $this->cache['__'][$_position]['value'];
    
            return $_success;
        }
    
        if (substr($this->string, $this->position, strlen(" ")) === " ") {
            $_success = true;
            $this->value = substr($this->string, $this->position, strlen(" "));
            $this->position += strlen(" ");
        } else {
            $_success = false;
        
            $this->report($this->position, '" "');
        }
        
        if ($_success) {
            $_value64 = array($this->value);
            $_cut65 = $this->cut;
        
            while (true) {
                $_position63 = $this->position;
        
                $this->cut = false;
                if (substr($this->string, $this->position, strlen(" ")) === " ") {
                    $_success = true;
                    $this->value = substr($this->string, $this->position, strlen(" "));
                    $this->position += strlen(" ");
                } else {
                    $_success = false;
                
                    $this->report($this->position, '" "');
                }
        
                if (!$_success) {
                    break;
                }
        
                $_value64[] = $this->value;
            }
        
            if (!$this->cut) {
                $_success = true;
                $this->position = $_position63;
                $this->value = $_value64;
            }
        
            $this->cut = $_cut65;
        }
    
        $this->cache['__'][$_position] = array(
            'success' => $_success,
            'position' => $this->position,
            'value' => $this->value
        );
    
        if (!$_success) {
            $this->report($_position, '__');
        }
    
        return $_success;
    }

    private function line()
    {
        return count(explode("\n", substr($this->string, 0, $this->position)));
    }

    private function rest()
    {
        return '"' . substr($this->string, $this->position) . '"';
    }

    protected function report($position, $expecting)
    {
        if ($this->cut) {
            $this->errors[$position][] = $expecting;
        } else {
            $this->warnings[$position][] = $expecting;
        }
    }

    private function expecting()
    {
        if (!empty($this->errors)) {
            ksort($this->errors);

            return end($this->errors)[0];
        }

        ksort($this->warnings);

        return implode(', ', end($this->warnings));
    }

    public function parse($_string)
    {
        $this->string = $_string;
        $this->position = 0;
        $this->value = null;
        $this->cache = array();
        $this->cut = false;
        $this->errors = array();
        $this->warnings = array();

        $_success = $this->parseOrExpr();

        if (!$_success) {
            throw new \InvalidArgumentException("Syntax error, expecting {$this->expecting()} on line {$this->line()}");
        }

        if ($this->position < strlen($this->string)) {
            throw new \InvalidArgumentException("Syntax error, unexpected {$this->rest()} on line {$this->line()}");
        }

        return $this->value;
    }
}