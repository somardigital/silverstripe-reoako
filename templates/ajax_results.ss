ajax

<% if $results %>
    <% with $results %>
    <h2 role="alert">
            There is $count result for "$search_term"
    </h2>
    <% end_with %>
<% end_if %>

<% if $error %>
    <p class="help-block help-warning">
        There is a problem connecting to Reoako. $error.
    </p>
<% end_if %>


<% if $results %>
    <% include results %>
<% end_if %>
