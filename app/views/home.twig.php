{% extends "master.twig.php" %}
{% block content %}
	<div class="container">
		<table class="table table-striped">
			<thead>
				<tr>
					<th scope="col">ID</th>
					<th scope="col">First Name</th>
					<th scope="col">Last Name</th>
					<th scope="col">Email</th>
				</tr>
			</thead>
			<tbody>
				{% for user in users %}
					<tr>
						<th scope="row">{{ user.id }}</th>
						<td>{{ user.firstName }}</td>
						<td>{{ user.lastName }}</td>
						<td>{{ user.email }}</td>
					</tr>
				{% endfor %}
			</tbody>
		</table>
		<hr>
		<div class="d-flex justify-content-center">{{ paginate|raw }}</div>
	</div>
{% endblock %}
