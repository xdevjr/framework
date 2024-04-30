{% extends "master.twig.php" %}
{% block content %}
  <h1>
    {% for num in arr %}
      {{ num }}
    {% endfor %}
  </h1>
  <hr>
    {{ paginate|raw }}
  {% endblock %}
  