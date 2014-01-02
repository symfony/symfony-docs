After Symfony calls ``createToken()``, it will then call ``supportsToken()``
on your class (and any other authentication listeners) to figure out who should
handle the token. This is just a way to allow several authentication mechanisms
to be used for the same firewall (that way, you can for instance first try
to authenticate the user via a certificate or an API key and fall back to
a form login).

Mostly, you just need to make sure that this method returns ``true`` for a
token that has been created by ``createToken()``. Your logic should probably
look exactly like this example.
