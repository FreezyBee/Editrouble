<?php

namespace FreezyBee\Editrouble;

use Latte\CompileException;
use Latte\Compiler;
use Latte\MacroNode;
use Latte\Macros\MacroSet;
use Latte\PhpWriter;

class Macros extends MacroSet
{
    public static function install(Compiler $compiler): void
    {
        $me = new self($compiler);

        $me->addMacro('editrouble', '', [$me, 'macroEditrouble']);
    }

    public function macroEditrouble(MacroNode $node, PhpWriter $writer): void
    {
        $name = $node->tokenizer->fetchWord();

        if (!$name) {
            throw new CompileException("Missing editrouble name in {{$node->name}}.");
        }

        $args = $writer->write('%node.args');
        $attrs = " data-editrouble='\" . json_encode([" . ($args ? $args . ',' : '') .
            "'name' => \"" . $name . "\"]) . \"'";

        preg_match('#(^.*?>)(.*)(<.*\z)#s', $node->content, $parts);

        $node->content = '<?php echo \'' . substr($parts[1], 0, -1) . '\' . '
            . '($presenter->editroubleConnector->checkPermission() ? '
            . '"' . $attrs . '" : \'\') . \'>\'; '
            . $writer->write('echo $presenter->editroubleConnector->getContent("' . $name . '", [%node.args])')
            . ' ?>'
            . $parts[3];
    }
}
