<?php

namespace vierbergenlars\SemVer\Internal;

abstract class AbstractVersion
{
    const MAJOR = 1;
    const MINOR = 2;
    const PATCH = 3;
    const PRERELEASE = 4;
    protected $M;
    protected $m;
    protected $p;
    protected $r;
    protected $b;

    public function __construct($M, $m, $p, array $r, array $b)
    {
        $this->M = $M;
        $this->m = $m;
        $this->p = $p;
        $this->r = $r;
        $this->b = $b;
    }

    static protected function create($version, $loose, $optionalParts)
    {
        if($loose) {
            $num = '[0-9]+';
        } else {
            $num = '0|[1-9]\\d*';
        }
        if($optionalParts) {
            $num.='|[xX*]';
        }
        $nonnum = '\\d*[a-zA-Z-][a-zA-Z0-9-]*';
        if($optionalParts) {
            $mainver = '('.$num.')(\\.('.$num.')(\\.('.$num.'))?)?';
        } else {
            $mainver = '('.$num.')(\\.('.$num.')(\\.('.$num.')))';
        }
        $prerelid = '(?:'.$num.'|'.$nonnum.')';
        $prerel = '(?:-('.$prerelid.'(?:\\.'.$prerelid.')*))';
        $bldid = '[0-9A-Za-z-]+';
        $bld = '(?:\\+(' . $bldid . '(?:\\.' . $bldid . ')*))';
        $full = '/^v?'.$mainver.$prerel.'?'.$bld.'?$/';

        if(!preg_match($full, $version, $matches)) {
            throw new \RuntimeException(sprintf('Bad version %s', $version));
        }
        $mc = count($matches);
        return new static($matches[1], $mc>3?$matches[3]:null, $mc>5?$matches[5]:null, $mc>6?explode('.', $matches[6]):array(), $mc>7?explode('.',$matches[7]):array());
    }

    public function getMajor()
    {
        return $this->M===null?null:(int)$this->M;
    }

    public function setMajor($M)
    {
        $t = clone $this;
        $t->M = $M;
        return $t;
    }

    public function getMinor()
    {
        return $this->m===null?null:(int)$this->m;
    }

    public function setMinor($m)
    {
        $t = clone $this;
        $t->m = $m;
        return $t;
    }

    public function getPatch()
    {
        return $this->p===null?null:(int)$this->p;
    }

    public function setPatch($p)
    {
        $t = clone $this;
        $t->p = $p;
        return $t;
    }

    public function getPreRelease()
    {
        return $this->r;
    }

    public function setPreRelease($r)
    {
        $t = clone $this;
        if(is_array($r)) {
            $t->r = $r;
        } else {
            $t->r = explode('.', $r);
        }
        return $t;
    }

    public function getBuild()
    {
        return $this->b;
    }

    public function setBuild($b)
    {
        $t = clone $this;
        if(is_array($b)) {
            $t->b = $b;
        } else {
            $t->b = explode('.', $b);
        }
        return $t;
    }

    public function unsetToZero()
    {
        $t = clone $this;
        $t->M = (int)$t->M;
        $t->m = (int)$t->m;
        $t->p = (int)$t->p;
        return $t;
    }

    /**
     *
     * @param Version $other
     * @return number Returns -1 when this object is smaller than $other, 0 when they are equal and 1 when this object is larger than $other
     */
    public function compare(AbstractVersion $other)
    {
        $maincmp = $this->compareMain($other);
        if($maincmp == 0) {
            return $this->comparePre($other);
        } else {
            return $maincmp;
        }
    }

    private function compareMain(AbstractVersion $other)
    {
        if($M = self::compareIdentifiers($this->getMajor(), $other->getMajor())) {
            return $M;
        }
        if($m = self::compareIdentifiers($this->getMinor(), $other->getMinor())) {
            return $m;
        }
        return self::compareIdentifiers($this->getPatch(), $other->getPatch());
    }

    private function comparePre(AbstractVersion $other)
    {
        $tp = $this->getPreRelease();
        $tl = count($tp);
        $op = $other->getPreRelease();
        $ol = count($op);

        if($tl > 0 && $ol == 0) {
            return -1;
        } else if($tl == 0 && $ol > 0) {
            return 1;
        } else if($tl == 0 && $ol == 0) {
            return 0;
        }

        $i = 0;
        do {
            if($i >= $tl && $i >= $ol) {
                return 0;
            } else if ($i >= $ol) {
                return 1;
            } else if($i >= $tl) {
                return -1;
            } else if($tp[$i] !== $op[$i]) {
                return self::compareIdentifiers($tp[$i], $op[$i]);
            }
        } while(++$i);
    }

    private static function compareIdentifiers($a, $b)
    {
        $anum = is_numeric($a);
        $bnum = is_numeric($b);

        if($anum && $bnum) {
            $a = (int)$a;
            $b = (int)$b;
        }

        if($a === null && $b !== null) {
            return -1;
        } elseif($a !== null && $b === null) {
            return 1;
        } elseif($a === null && $b === null) {
            return 0;
        }

        if($anum && !$bnum) {
            return -1;
        } elseif($bnum && !$anum) {
            return 1;
        } elseif($a < $b) {
            return -1;
        } elseif($a > $b) {
            return 1;
        } else {
            return 0;
        }
    }

    public function increment($release)
    {
        $M = $this->getMajor();
        $m = $this->getMinor();
        $p = $this->getPatch();
        $r = $this->getPreRelease();
        switch($release) {
            case self::MAJOR:
                $M++;
                $m = -1;
                // no break
            case self::MINOR:
                $m++;
                $p = -1;
                // no break
            case self::PATCH:
                $p++;
                $r = array();
                break;
            case self::PRERELEASE:
                if(($i = count($r)) == 0) {
                    $r = array(0);
                } else {
                    for (;$i >= 0; $i--) {
                        if(is_numeric($r[$i])) {
                            $r[$i]++;
                            break 2; // exit for and switch
                        }
                    }
                    $p[] = 0;
                }
                break;
            default:
                throw new \LogicException('Invalid increment argument');
        }

        return new static($M, $m, $p, $r, array());
    }

    protected static function isX($c)
    {
        return strtolower($c) === 'x' || $c === '*';
    }

    public function __toString()
    {
        $v = (int)$this->M;
        if($this->m !== null) {
            $v.='.'.(int)$this->m;
            if($this->p !== null) {
                $v.='.'.(int)$this->p;
            }
        }
        if($this->r !== array()) {
            $v .= '-' . implode('.', $this->r);
        }

        if($this->b !== array()) {
            $v .= '+' . implode('.', $this->b);
        }

        return (string)$v;
    }

}
