<span class="taxonomy-list-item" itemscope itemtype="https://schema.org/DefinedTerm">
<% if $IconPath %>
    <img src="{$IconPath}">
<% else_if $TaxonomyIconCssClass %>
    <span class="{$TaxonomyIconCssClass}"></span>
<% end_if %>
    <span itemprop="name">{$Name}</span>
<% if $Description %>
    - <span itemprop="description">{$Description}</span>
<% end_if %>
    <link itemprop="inDefinedTermSet" href="#{$DefinedTermSet}">
</span>
