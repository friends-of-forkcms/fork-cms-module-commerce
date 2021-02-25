{*
	variables that are available:
	- {$widgetCommerceRecentProducts}: contains an array with all products, each element contains data about the product
*}

{option:widgetCommerceRecentProducts}
	<section id="blogRecentProductsWidget" class="mod">
		<div class="inner">
			<header class="hd">
				<h3>{$lblRecentProducts|ucfirst}</h3>
			</header>
			<div class="bd content">
				<ul>
					{iteration:widgetCommerceRecentProducts}
						<li><a href="{$widgetCommerceRecentProducts.full_url}" title="{$widgetCommerceRecentProducts.title}">{$widgetCommerceRecentProducts.title}</a></li>
					{/iteration:widgetCommerceRecentProducts}
				</ul>
			</div>
		</div>
	</section>
{/option:widgetCommerceRecentProducts}
