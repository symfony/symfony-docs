Respectful Review Comments
==========================

:doc:`Reviewing issues and pull requests </contributing/community/reviews>`
is a great way to get started with contributing to the Symfony community.
Anyone can do it! But before you give a comment, take a step back and think,
is what you are about to say actually what you intend?

Communicating over the Internet with nothing but text can pose a
big challenge, especially if you remember that the Symfony community
is world-wide and is composed of a wide variety of people with differing
ideas and opinions.

Not everyone speaks English or is able to use a keyboard. Some might
have dyslexia or similar conditions that affect their writing.

Not to mention that some might have a bad experience from previous
contributions (to other projects).

You're not alone in this. This guide will try to help you write
constructive, respectful and helpful reviews and replies.

.. tip::

    This guide is not about lecturing you to "conform" or give-up
    your ideas and opinions but helping you to better communicate,
    prevent possible confusion, and keeping the Symfony community a
    welcoming place for everyone. **You are free to disagree with
    someone's opinions, but don't be disrespectful.**

It’s important to accept that many programming decisions are opinions.
Discuss trade-offs, which you prefer, and reach a resolution quickly.
It's not about being right or wrong, but using what works.

Tone of Voice
-------------

We don't expect you to be completely formal, or to even write error-free
English. Just remember this: don't swear, and be respectful to others.

Don't reply in anger or with an aggressive tone. If you're angry, we understand
that, but swearing/cursing and name calling doesn't really encourage anyone to
help you. Take a deep breath, count to 10 and try to *clearly* explain what problems
you encounter.

Inclusive Language
------------------

In an effort to be inclusive to a wide group of people, it's recommended to
use personal pronouns that don't suggest a particular gender. Unless someone
has stated their pronouns, use "they", "them" instead of "he", "she", "his",
"hers", "his/hers", "he/she", etc.

Try to avoid using wording that may be considered excluding, needlessly gendered
(e.g. words that have a male or female base), racially motivated or singles out
a particular group in society. For example, it's recommended to use words like
"folks", "team", "everyone" instead of "guys", "ladies", "yanks", etc.

Giving Positive Feedback
------------------------

While reviewing issues and pull requests you may run into some suggestions
(including patches) that don't reflect your ideas, are not good, or downright wrong.

Now, when you prepare your comment, consider the amount of work and time the author
has spent on their idea and how your response would make them feel.

Did you correctly understand their intention? Or are you making assumptions?
Whatever your response, be explicit. Remember people don't always understand your
intentions online.

Avoid using terms that could be seen as referring to personal traits ("dumb", "stupid").
Assume everyone is intelligent and well-meaning.

.. tip::

    Good questions avoid judgment and avoid assumptions about the author's perspective.

    Maybe you can ask for clarification? Suggest an alternative?
    Or provide a simple explanation *why* you disagree with their proposal.

    * ``This looks wrong. Are you sure it's correct?`` (e.g. typo/syntax error)

    * ``What do you think of "RequestFactory" instead of RequestCreator?``

Even if something *is* really wrong or "a bad idea", stay respectful and
don't get into endless you-are-wrong discussions or "flame wars".

Don't use hyperbole ("always", "never", "endlessly", "nothing", "worst", "horrible", "terrible").

**Don't:** *"I don't like how you wrote this code"* - there is no clear explanation why you
don't like how it's written.

**Better:** *"I find it hard to read this code as there are many nested if statements, can you make it more
readable? By encapsulating some of the details or maybe adding some comments to explain the overall logic."* -
You explain why you find the code hard to read *and* give some suggestions for improvement.

If a piece of code is in fact wrong, explain why:

* "This code doesn't comply with Symfony's CS rules. Please see [...] for details."

* "Symfony 3 still uses PHP 5 and doesn't allow the usage of scalar type-hints."

* "I think the code is less readable now." - careful here, be sure explain why you think
  the code is less readable, and maybe give some suggestions?

**Examples of valid reasons to reject:**

* "We tried that in the past (link to the relevant PR) but we needed to revert it for XXX reason."

* "That change would introduce too many merge conflicts when merging up Symfony branches.
  In the past we've always rejected changes like this."

* "I profiled this change and it hurts performance significantly" - if you don't profile, it's an opinion, so we can ignore

* "Code doesn't match Symfony's CS rules (e.g. use ``[]`` instead of ``array()``)"

* "We only provide integration with very popular projects (e.g. we integrate Bootstrap but not your own CSS framework)"

* "This would require adding lots of code and making lots of changes for a feature that doesn't look so important.
  That could hurt maintenance in the future."

Asking for Changes
------------------

Rarely something is perfect from the start, while the code itself is good.
It may not be optimal or conform to the Symfony coding style.

Again, understand the author already spent time on the issue and asking
for (small) changes may be misinterpreted or seen as a personal attack.

Be thankful for their work (so far), stay positive and really help them
to make the contribution a great one. *Especially if they are a first
time contributor.*

Use words like "Please", "Thank you" and "Could you" instead of making demands;

* "Thank you for your work so far. I left some suggestions for improvement
  to make the code more readable."

* "Your code contains some coding-style problems, can you fix these before
  we merge? Thank you"

* "Please use 4 spaces instead of tabs", "This needs be on the previous line";

During a pull request review you can usually leave more than one comment,
you don't have to use "Please" all the time. But it wouldn't hurt.

It may not seem like much, but saying "Thank you" does make others feel
more welcome.


Preventing Escalations
----------------------

Sometimes when people receive feedback they may get defensive.
In that case, it is better to try to approach the discussion in
a different way, to not escalate further.

If you want someone to mediate, please join the ``#contribs`` channel on `Symfony Slack`_,
to have a safe environment and keep working together on common goals.

Using Humor
-----------

In short: Extreme misbehavior will not be tolerated and may even get you banned;
Keep it real and friendly.

**Don't use sarcasm for a serious topic, that's not something that belongs
to the Symfony community.** And don't marginalize someone's problems;
``Well I guess that's not supposed to happen? 😆``.

Even if someone's explanation is "inviting to joke about it", it's a real
problem to them. Making jokes about this doesn't help with solving their
problem and only makes them *feel stupid*. Instead, try to discover the
actual problem.

Final Words
-----------

Don't feel bad if you "failed" to follow these tips. As long as your
intentions were good and you didn't really offend or insult anyone;
you can explain you misunderstood, you didn't mean to marginalize or
simply failed.

But don't say it "just because", if your apology is not really meant
you *will* lose credibility and respect from other developers.

*Do unto others as you would have them do unto you.*

.. _`Symfony Slack`: https://symfony.com/slack-invite
