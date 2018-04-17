# Contributing

This is an open source project.
We appreciate any help from the community to improve the it.

### Bugs or new ideas

If you have found a bug, or have an idea for a new feature or to enhance existing features, you can submit them here:

- [WordPress Forum](https://wordpress.org/support/plugin/sermon-manager-for-wordpress)
- [Bug Tracker](https://github.com/WP-for-Church/Sermon-Manager/issues)

Contributions via [pull request](https://github.com/WP-for-Church/Sermon-Manager/pulls),
and [bug reports](https://github.com/WP-for-Church/Sermon-Manager/issues) are welcome!
Please submit your pull request to the `develop` branch and use the GitHub issue tracker to report issues.

**Note!** If you have detected any security issues, please write an email to nikola@wpforchurch.com. Do not submit it on the 
public forum or in a public GitHub issue.

### Translations

Is the plugin not available in your language or are some translations missing?
Well you can change that by adding or modifying a translation:

We use GlotPress (official WordPress translation tool) as a translation platform. 
Create an account on [wordpress.org](wordpress.org) and you can start translating right away at 
[Sermon Manager's page](https://translate.wordpress.org/projects/wp-plugins/sermon-manager-for-wordpress).
No coding skills are required at all.

(Our integration with GlotPress is still not ready)

# Development

The default branch for the Sermon Manager repository on GitHub is **"master"**, while there is another important branch
called **"dev"** (shortened for "develop"). Each of them serves their own purpose.

### master branch
The **"master"** branch is a stable branch, and gets updated only on releases. Whenever people checkout/download the 
**"master"** branch, they get the source code of the latest official release of the Sermon Manager. (same as if they 
downloaded the latest version on WordPress)

### dev branch
The **"dev"** branch, is where commits during development are integrated into. It is where the WP For Church team
pushes or merges their actual changes together and where contributions from the community (Pull requests) are
integrated into the development version of the plugin. Anyone who wish to try the cutting edge version of Sermon Manager
can download the develop branch and install it on their website.

(Note: whenever a commit is created on develop branch, a development zip package is created by WordPress, which can be 
downloaded from [here](https://downloads.wordpress.org/plugin/sermon-manager-for-wordpress.zip).)

Pull requests are always merged into the **"dev"** branch. If you are willing to contribute, make sure that you are 
sending us pull requests against the dev branch but not the *master* branch.

### GIT Flow
They are named *master* and *dev* because most of the core developers adapt the git flow convention when working
on Sermon Manager. When working on a feature that is likely taking quite some time to finish, a local feature branch is
created, and not necessarily pushed to GitHub. This way, when there are pending pull requests, they do not have to
wait too long, since they can be merged into develop branch first.

An introduction of git-flow can be found [here](http://nvie.com/posts/a-successful-git-branching-model/) or
[here](https://www.atlassian.com/git/tutorials/comparing-workflows/gitflow-workflow).

You do not necessarily have to adopt git-flow for yourself in order to contribute to the plugin, as long as your changes
use the branch **"dev"** as a base and the pull request is against the **"dev"** branch, we will be able to integrate your
changes easily.

#### In short:

- Big features get developed on **feature branches**, either in your local repository or pushed to GitHub. Feature branches
can be rebased.
- Once ready, **feature branches** are PR'd to **dev**.
- When the WPFC team wants to make a release, **dev** is branched into a **release branch**. Version gets bumped, necessary
stabilization work happens, including final changes and testing, on that branch. (**dev** is never frozen, and efforts to PR 
in **feature branches** should not stop just because a release is happening)
- When a release is ready to be released, the **release branch** is merged into **dev** & **master**, **master** is tagged 
at that point.
- If hotfixes need to be made, then a **hotfix branch** is created from **master** and all necessary fixes are applied on it.
After the critical bug has been fixed and version has been bumped, **hotfix branch** is merged into **master** and **dev**, 
and **master** is tagged with at that point. 
- **dev** and **master** are protected; no rebasing happens there.
