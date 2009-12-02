<?php
// Documentation located in README. Put your info in first 3 lines.
class Blog {
	private $password = "breck";
	public $blog_title = "Breck Yunits' Blog";
	public $blog_description = "My weblog where I write my thoughts.";
	public function __construct()
	{
		include("posts.php");
		$this->posts = $posts;
		foreach ($posts as $key => $array)
		{
			$this->titles[strtolower(str_replace(" ","_",preg_replace('/[^a-z0-9 ]/i',"",$array['Title'])))] = $key;
		}
	}
	public function saveBlog()
	{
		if (isset($_POST['password']) && $_POST['password'] == $this->password)
		{
			if (!isset($_GET['post'])) // create new post
			{
				$this->posts[time()] = array("Title" => $_POST['title'], "Essay" => $_POST['essay']);
				file_put_contents("posts.php", "<?php \$posts= ".var_export($this->posts, true) . "?>");
			}
			else { // edit posts
			
			}
		}
	}
	public function displayEditor ()
	{
		$title = "";
		$essay = "";
		if (isset($_GET['post']) && isset($this->posts[$_GET['post']]))
		{
			$title = $this->posts[$_GET['post']]['Title'];
			$essay = $this->posts[$_GET['post']]['Essay'];
		}
		$content = <<<LONG
		<form method="post" action="">
		<table>
		<tr><td>Title</td><td><input type="text" name="title" size="25" value="$title"></td></tr>
		<tr><td>Content</td><td><textarea name="essay" rows="30" cols="80">$essay</textarea></td></tr>
		<tr><td>Password</td><td><input type="password" name="password"></td></tr>
		<tr><td></td><td><input type="submit" value="Save"></td></tr></table>
		</form>
		Edit a Post:<br>
LONG;
		$edit_posts = "";
		foreach ($this->posts as $key => $array)
		{
			$edit_posts .= "<a href=\"write?post=".$key."\">{$array['Title']}</a><br>";
		}
		$this->displayPage("Editor","Edit your blog",$content . $edit_posts);
	}
	public function controller()
	{
		if (isset($_GET['r']) && $_GET['r'] == "/write")
		{
			$this->saveBlog();
			$this->displayEditor();
		}
		elseif (isset($_GET['r']) && isset($this->titles[substr($_GET['r'],1)]) )
		{
			$post = $this->posts[$this->titles[substr($_GET['r'],1)]];
			$this->displayPage($post['Title'],substr($post['Essay'],0,100),
			"<h1>{$post['Title']}</h1><div>{$post['Essay']}</div>");
		}
		else { // Homepage
			$last_five = "";
			foreach ($this->posts as $post)
			{
				$last_five .= "<h1><a href=\"".strtolower(str_replace(" ","_",preg_replace('/[^a-z0-9 ]/i',"",$post['Title'])))."\">{$post['Title']}</a></h1><div>{$post['Essay']}</div>";
			}
			$this->displayPage($this->blog_title, $this->blog_description,
			$last_five); 
		}
	}
	public function displayPage($title, $description, $body)
	{
		?>
			<html>
			<head>
			<style>
			body {
				font-family: Georgia;
				color: #888888;
			}
			#sidebar {
				float: right;
			}
			</style>
			<title><?php echo $title;?></title>
			<meta name="description" content="<?php echo $description;?>">
			</head>
			<body>
			<?php
			echo $body;
			?>
			<div id="sidebar">
				<a href="/">Home</a><br>
				<?php foreach ($this->posts as $post)
				{
					?><a href="/<?php 
					echo strtolower(str_replace(" ","_",preg_replace('/[^a-z0-9 ]/i',"",$post['Title'])));
					?>"><?php echo $post['Title'];?></a><br><?php
				}
				?>
				<br><a href="/write" rel="nofollow">Admin</a><br>
			</div>
			</html>
		<?php
	}
}
$blog = new Blog;
$blog->controller();
?>