{*
	variables that are available:
	- {$firstName}: first name of person that ordered
	- {$commerceUrl}: url to index page
*}
<div class="thank-you">
    <h1>{$lblThanks|ucfirst}, {$firstName}!</h1>
    <a href="{$commerceUrl}">{$msgToCommerceOverview}</a>
</div>
