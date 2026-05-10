<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title','dashboard')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
</head>
<body>

@if(session('success'))
<div class="alert alert-success" style="color:green">
  {{session('success')}}
</div>
@endif

    @include('partial.nav')
<div>
  @section('content')
 this is dash
  @show()
</div>

<x-butto>
  omar
</x-butto>

<form class="row g-3" method="post"action="{{route('store')}}">
   @csrf
  <div class="col-md-6">
    <label for="inputEmail4" class="form-label">Email</label>
    <input type="email" class="form-control" id="inputEmail4" name="email">
    @error('email')
    <div style="color:red">{{$message}}</div>
    @enderror
  </div>
  <div class="col-md-6">
    <label for="inputPassword4" class="form-label">Password</label>
    <input type="password" class="form-control" id="inputPassword4" name="password">
  </div>
  <div class="col-md-6">
  <label for="inputEmail4" class="form-label">Name</label>
    <input type="text" class="form-control" id="inputEmail4" name="name">
   
  </div>
  
  
  <div class="col-12">
    <button type="submit" class="btn btn-primary">Sign in</button>
  </div>
</form>



</body>
</html>