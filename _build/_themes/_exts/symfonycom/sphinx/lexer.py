from pygments.lexer import RegexLexer, bygroups, using
from pygments.token import *
from pygments.lexers.shell import BashLexer, BatchLexer

class TerminalLexer(RegexLexer):
    name = 'Terminal'
    aliases = ['terminal']
    filenames = []

    tokens = {
        'root': [
            ('^\$', Generic.Prompt, 'bash-prompt'),
            ('^>', Generic.Prompt, 'dos-prompt'),
            ('^#.+$', Comment.Single),
            ('^.+$', Generic.Output),
        ],
        'bash-prompt': [
            ('(.+)$', bygroups(using(BashLexer)), '#pop')
        ],
        'dos-prompt': [
            ('(.+)$', bygroups(using(BatchLexer)), '#pop')
        ],
    }
