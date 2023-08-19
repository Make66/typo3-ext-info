.. include:: ../Includes.txt

.. _known-problems:

==============
Known Problems
==============

Security check
==============
using exec() to call shell commands "find" and "grep" safes a lot of code and processing time.
But how safe is it really?
What does a windows box say to this?
What does a small hosting package say to this?
More checks needed...
Shall we backport to Typo3 v9?

Root- and all templates
=======================
Currently sorted by pid/siteroot DESC instead of ASC

Domain check
============
currently uses cURL from an action and expects a HTTP response code of <400 to be successful. Timeout isset to 3 seconds.
Are results always trustable?
