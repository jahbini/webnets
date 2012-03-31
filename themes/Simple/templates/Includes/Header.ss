<header class="header" role="banner">
	<div class="inner">
		<a href="$BaseHref" class="brand" rel="home">
<% if SubDomain %>
	   		<h1>$SiteConfig.Title</h1><h2 >-- $SubDomain.HeadLine</h2>
			<p>$SiteConfig.Tagline -- $SubDomain.Slogan</p>
<% else %>
	   		<h1>$SiteConfig.Title</h1>
			<p>$SiteConfig.Tagline</p>
<% end_if %>
		</a>
		<% include Navigation %>
	</div>
</header>
