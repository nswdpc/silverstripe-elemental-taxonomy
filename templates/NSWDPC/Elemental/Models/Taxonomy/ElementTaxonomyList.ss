<% if $SelectedTerms %>
<div class="content-element__content<% if $StyleVariant %> {$StyleVariant}<% end_if %>">
    <% include ElementTaxonomyTitle %>
    <div itemscope itemtype="https://schema.org/DefinedTermSet" id="{$Anchor}-definedtermset">
        <% if $ShowTypename %>
            <h4 itemprop="name">>{$TaxonomyType.Name}</h4>
        <% end_if %>
        <ol>
        <% loop $SelectedTerms %>
            <li><% include TaxonomyListItem DefinedTermSet=$Up.DefinedTermSet %></li>
        <% end_loop %>
        </ol>
    </div>
</div>
<% end_if %>
