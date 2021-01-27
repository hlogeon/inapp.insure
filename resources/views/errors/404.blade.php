<!DOCTYPE html>
<html>
  	<head>
  		<title>404 page</title>
  	  	@include("global.header")
	</head>
<body style="padding-top: 80px;">
	<div id="app">
		<div>
			<v-header />
		</div>
		<div>
			<main>
			  <!-- not-found -->
			  <section class="not-found-section">
			    <div class="container">
			      <!-- wrapper -->
			      <div class="not-found-wrapper">
			        <!-- content -->
			        <div class="not-found-content">
			          <h1 class="title">Упс... Ошибка 404</h1>
			          <!-- info -->
			          <div class="not-found-info">
			            Ты не пройдешь здесь.<br />
			            Такой странцы не существует :)
			          </div>
			          <!-- ./ End of info -->
			          <!-- buttons -->
			          <div class="not-found-buttons">
			            <a href="/" class="button button--small">Вернуться на главную</a>
			          </div>
			          <!-- ./ End of buttons -->
			        </div>
			        <!-- ./ End of content -->
			        <!-- image -->
			        <div class="not-found-image">
			          <img src="/images/not-found_img1.svg" />
			        </div>
			        <!-- ./ End of image -->
			      </div>
			      <!-- ./ End of wrapper -->
			    </div>
			    <!-- ./ End of container -->
			  </section>
			  <!-- ./ End of not-found -->
			</main>
		</div>
		<div>
			<v-footer />
		</div>
	</div>
	
	@include("global.scripts")

	<script>
		setTimeout(() => {
			document.querySelectorAll('a').forEach(($link) => {
				$link.addEventListener('click', function(event){
					event.preventDefault()
					try {
						window.location.href = this.getAttribute('href')
					} catch(e) {}
				})
			})
		}, 1000)
	</script>
</body>
</html>