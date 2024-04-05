# Save old articles aliases Joomla 4 / Joomla 5 console plugin
The plugin updates the aliases of Joomla articles by adding the article id to them, according to the old Joomla routing rules. This will allow you to save the old URLs of the articles and at the same time transfer   Joomla site to a new router.

# Why use this plugin
## The problem with the id in the URL of Joomla materials on old sites and old router
In older versions of Joomla, the URL was formed according to the scheme `material id + material alias`. For example, `145-my-article-alias`. However. the old Joomla router was not perfect and produced duplicates of pages, which SEO specialists fought hard with the help of various plugins and hacks of the CMS core.
Starting with Joomla 3.8, a new router was included in the kernel, devoid of these disadvantages. He was also distinguished by the fact that he removed the article id from the URL. And this, in turn, led to problems on large sites, since they already had many pages in the search engine index. Therefore, even when updating the site to Joomla 4 and Joomla 5, many old sites had to leave the old router on.
## The solution to the problem
The solution to the problem is quite simple. You need to save the Joomla content id to aliases and then disable the old Joomla router. This is exactly what this plugin does, allowing you to process thousands of articles in a few seconds. In the database, the article id is added to each alias in front, which brings it to the view that the old Joomla router formed.
This way the page URL will be saved, but a new router will be running under the hood of Joomla.

**You do this work after your site has been successfully updated to at least Joomla 4. The plugin will only work with Joomla 4 and higher.**

# How to use
Connect to your server via SSH (this article can helps you [Joomla 4: A Powerful CLI Application](https://magazine.joomla.org/all-issues/june-2022/joomla-4-a-powerful-cli-application) ), go to the CLI folder of your site and run the command `php joomla.php oldarticlesaliases:save`. In this case, **all** articles on the site will be processed without exceptions.

If you add the `test` argument - `php joomla.php oldarticlesaliases:save test` - then you will see exactly what changes will be made by the plugin, but the changes themselves will not be applied.

In order to process articles of only selected categories, specify the `id` of these categories in the option `--cats` separated by commas without spaces. For example, `--cats=12,140,211`. The command in this case will look like `php joomla.php oldarticlesaliases:save --cats=12,140,211`.

# Be careful!
The plugin works with the database directly. Be sure to make a backup copy of the database before you start working!
Call the command of this plugin only 1 (one) time, otherwise duplicates of article IDs in aliases like `145-145-my-article-alias` may occur.
