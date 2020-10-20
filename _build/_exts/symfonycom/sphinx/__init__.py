from pygments.style import Style
from pygments.token import Keyword, Name, Comment, String, Error, \
     Number, Operator, Generic, Whitespace, Punctuation, Other, Literal

class SensioStyle(Style):
    background_color = "#000000"
    default_style = ""

    styles = {
        # No corresponding class for the following:
        #Text: "", # class: ''
        Whitespace: "underline #f8f8f8", # class: 'w'
        Error: "#a40000 border:#ef2929", # class: 'err'
        Other: "#ffffff", # class 'x'

    Comment: "italic #B729D9", # class: 'c'
    Comment.Single: "italic #B729D9", # class: 'c1'
    Comment.Multiline: "italic #B729D9", # class: 'cm'
    Comment.Preproc: "noitalic #aaa", # class: 'cp'

    Keyword: "#FF8400", # class: 'k'
        Keyword.Constant: "#FF8400", # class: 'kc'
        Keyword.Declaration: "#FF8400", # class: 'kd'
        Keyword.Namespace: "#FF8400", # class: 'kn'
        Keyword.Pseudo: "#FF8400", # class: 'kp'
        Keyword.Reserved: "#FF8400", # class: 'kr'
        Keyword.Type: "#FF8400", # class: 'kt'

    Operator: "#E0882F", # class: 'o'
    Operator.Word: "#E0882F", # class: 'ow' - like keywords

    Punctuation: "#999999", # class: 'p'

        # because special names such as Name.Class, Name.Function, etc.
        # are not recognized as such later in the parsing, we choose them
        # to look the same as ordinary variables.
        Name: "#ffffff", # class: 'n'
    Name.Attribute: "#ffffff", # class: 'na' - to be revised
    Name.Builtin: "#ffffff", # class: 'nb'
        Name.Builtin.Pseudo: "#3465a4", # class: 'bp'
    Name.Class: "#ffffff", # class: 'nc' - to be revised
        Name.Constant: "#ffffff", # class: 'no' - to be revised
        Name.Decorator: "#888", # class: 'nd' - to be revised
        Name.Entity: "#ce5c00", # class: 'ni'
        Name.Exception: "#cc0000", # class: 'ne'
    Name.Function: "#ffffff", # class: 'nf'
        Name.Property: "#ffffff", # class: 'py'
        Name.Label: "#f57900", # class: 'nl'
        Name.Namespace: "#ffffff", # class: 'nn' - to be revised
    Name.Other: "#ffffff", # class: 'nx'
    Name.Tag: "#cccccc", # class: 'nt' - like a keyword
    Name.Variable: "#ffffff", # class: 'nv' - to be revised
        Name.Variable.Class: "#ffffff", # class: 'vc' - to be revised
        Name.Variable.Global: "#ffffff", # class: 'vg' - to be revised
        Name.Variable.Instance: "#ffffff", # class: 'vi' - to be revised

    Number: "#1299DA", # class: 'm'

        Literal: "#ffffff", # class: 'l'
        Literal.Date: "#ffffff", # class: 'ld'

        String: "#56DB3A", # class: 's'
        String.Backtick: "#56DB3A", # class: 'sb'
        String.Char: "#56DB3A", # class: 'sc'
    String.Doc: "italic #B729D9", # class: 'sd' - like a comment
        String.Double: "#56DB3A", # class: 's2'
        String.Escape: "#56DB3A", # class: 'se'
        String.Heredoc: "#56DB3A", # class: 'sh'
        String.Interpol: "#56DB3A", # class: 'si'
        String.Other: "#56DB3A", # class: 'sx'
        String.Regex: "#56DB3A", # class: 'sr'
    String.Single: "#56DB3A", # class: 's1'
        String.Symbol: "#56DB3A", # class: 'ss'

        Generic: "#ffffff", # class: 'g'
        Generic.Deleted: "#a40000", # class: 'gd'
        Generic.Emph: "italic #ffffff", # class: 'ge'
        Generic.Error: "#ef2929", # class: 'gr'
        Generic.Heading: "#000080", # class: 'gh'
        Generic.Inserted: "#00A000", # class: 'gi'
        Generic.Output: "#888", # class: 'go'
        Generic.Prompt: "#745334", # class: 'gp'
        Generic.Strong: "bold #ffffff", # class: 'gs'
        Generic.Subheading: "bold #800080", # class: 'gu'
        Generic.Traceback: "bold #a40000", # class: 'gt'
    }
