How to add "Remember Me" Login Functionality
============================================

This article has not been written yet, but will soon. If you're interested
in writing this entry, see :doc:`/contributing/documentation/overview`.

This topic is meant to show how the ``remember_me`` firewall listener can
be used. It should also talk about how this affects the authentication process
and how the three authentication-roles (``IS_AUTHENTICATED_ANONYMOUSLY``,
``IS_AUTHENTICATED_REMEMBERED``, and ``IS_AUTHENTICATED_FULLY``) can be used.
This should be spun into a full example. For example, how can you use the
remember me functionality to allow a user to come back to a site to be recognized
(and have admin/account URLs visible), but then actually make the user authenticate
fully before making heavier account changes.