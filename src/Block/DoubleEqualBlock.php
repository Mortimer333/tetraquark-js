<?php declare(strict_types=1);

namespace Tetraquark\Block;
use \Tetraquark\Log as Log;
use \Tetraquark\Contract as Contract;
use \Tetraquark\Abstract\BlockAbstract as Block;

class DoubleEqualBlock extends Block implements Contract\Block
{
    public function objectify(int $start = 0)
    {
        $this->setInstruction('==');
        $this->setName('');
        $this->setInstructionStart($start);
        $this->setCaret($start);
    }

    public function recreate(): string
    {
        return '==';
    }
}
