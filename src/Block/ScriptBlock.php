<?php declare(strict_types=1);

namespace Tetraquark\Block;
use \Tetraquark\{Log as Log, Exception as Exception, Contract as Contract, Validate as Validate};
use \Tetraquark\Foundation\BlockAbstract as Block;

class ScriptBlock extends Block implements Contract\Block
{
    protected array  $endChars = [];
    /** @var string Minified script */
    protected string $minified = '';

    public function __construct(
        string $content,
        protected int    $start = 0,
        protected string $subtype = '',
        protected array  $data  = []
    ) {
        self::$content = $content;
        parent::__construct($start, $subtype, $data);
    }

    public function objectify(int $start = 0)
    {
        Log::timerStart();
        Log::log("Prepare file...");
        $this->prepare();
        Log::log("Mapping...");
        $this->map($start);
        Log::log("=======================");
        Log::log("Creating aliases...");
        $this->generateAliases();
        Log::log("=======================");
        $aliasesStr = "Aliases: ";
        foreach (self::$mappedAliases as $key => $value) {
            $aliasesStr .= "$key => $value, ";
        }
        Log::log($aliasesStr);
        Log::log("Recreating...");
        $this->setMinified($this->recreate());
        Log::log("=======================");
        Log::displayBlocks($this->blocks);
        Log::timerEnd();
    }

    protected function prepare(): void
    {
        self::$content = str_replace("\r","\n", self::$content);
        self::$content = preg_replace('/[\n]+/',"\n", self::$content);
        self::$content = preg_replace('/[ \t]+/', ' ', self::$content);
    }

    protected function map($start): void
    {
        $map    = [];
        $word   = '';
        $this->setCaret($start);
        $this->blocks = array_merge($this->blocks, $this->createSubBlocks());
    }

    protected function setMinified(string $minified): self
    {
        $this->minified = $minified;
        return $this;
    }

    public function getMinified(): string
    {
        return $this->minified;
    }

    public function recreate(): string
    {
        $script = '';
        foreach ($this->blocks as $block) {
            $script .= $block->recreate();
        }
        return $this->removeAdditionalSpaces($script);
    }
}
