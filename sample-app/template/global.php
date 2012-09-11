<!DOCTYPE HTML>
<html lang="fr">
<head>
	<title><?=$this->getMetaStandard("title")?></title>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<meta name="description" content="<?=$this->getMetaStandard("description")?>" />
	<meta name="keywords" content="<?=$this->getMetaStandard("keywords")?>" />
	<?=$this->getMetaAdditional()?>

	<?=$this->getCss()?>

	<?=$this->getJs()?>
</head>
<body>

	<div class="fluid">

		<header>
			<?=$this->render('header')?>
		</header>

		<div class="fixed">


			<div class="content container12">
				<section>
					<?=$this->content?>
				</section>
			</div>

			<footer>
				<?=$this->render('footer')?>
			</footer>
		</div>
	</div>


</body>
</html>
