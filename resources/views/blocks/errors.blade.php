@if($errors->any())
<div class="alert alert-danger alert-dismissible fade show">
	<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
	<ul class="" style="list-style: none; margin: 0; padding: 0;">
		@foreach($errors->all() as $error)
			<li class="">{{ $error }}</li>
		@endforeach
	</ul>
</div>
@endif
