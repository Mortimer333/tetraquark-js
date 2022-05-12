<?php declare(strict_types=1);

namespace Tetraquark\Block;
use \Tetraquark\{Log, Exception, Contract, Validate};
use \Tetraquark\Foundation\BlockAbstract as Block;

class ObjectBlock extends Block implements Contract\Block
{
    protected array $endChars = [
        "}" => true
    ];

    public function objectify(int $start = 0)
    {
        $this->setName('');
        $this->setCaret($start + 1);
        $this->setInstruction(new Content(''));
        $this->setInstructionStart($start);
        $this->blocks = array_merge($this->blocks, $this->createSubBlocks($start + 1));
    }

    public function recreate(): string
    {
        $script = '{';

        foreach ($this->getBlocks() as $block) {
            $script .= trim($block->recreate());
        }

        $script = rtrim($script, ',');

        return $script . '};';
    }
}
