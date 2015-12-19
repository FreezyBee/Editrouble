<?php

namespace FreezyBee\Editrouble;

use Latte\CompileException;
use Latte\Compiler;
use Latte\MacroNode;
use Latte\Macros\MacroSet;
use Latte\PhpWriter;

/**
 * Class Macros
 * @package FreezyBee\Editrouble
 */
class Macros extends MacroSet
{
    /**
     * @param Compiler $compiler
     */
    public static function install(Compiler $compiler)
    {
        $me = new static($compiler);

        $me->addMacro('editrouble', '', [$me, 'macroEditrouble']);
    }

    /**
     * @param MacroNode $node
     * @param PhpWriter $writer
     * @throws CompileException
     */
    public function macroEditrouble(MacroNode $node, PhpWriter $writer)
    {
        dump($node->tokenizer);

        $name = $node->tokenizer->fetchWord();

        if ($name === false) {
            throw new CompileException("Missing editrouble name in {{$node->name}}.");
        }

        $attrs = " data-editrouble='\" . json_encode([" . $writer->write('%node.args') .
            ", 'name' => '" . $name . "']) . \"'";

        preg_match('#(^.*?>)(.*)(<.*\z)#s', $node->content, $parts);

        $node->content = '<?php echo \'' . substr($parts[1], 0, -1) . '\' . '
            . '($_presenter->editroubleConnector->checkPermission() ? '
            . '"' . $attrs . '" : \'\') . \'>\'; '
            . $writer->write('echo $_presenter->editroubleConnector->getContent("' . $name . '", [%node.args])')
            . ' ?>'
            . $parts[3];
    }
}
