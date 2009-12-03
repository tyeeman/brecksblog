<?php
// Documentation located in README.
class Blog {
	private $blog_password = "breck";
	public $blog_title = "Breck Yunits' Blog";
	public $blog_description = "My weblog where I write my thoughts.";
	public function __construct()
	{
		include("posts.php");
		$this->posts = $posts;
		foreach ($posts as $key => $array) // Necessary for the pretty urls
		{
			$this->titles[$this->prettyUrl($array['Title'])] = $key;
		}
	}
	public function prettyUrl($title_string) // Turns a Super-Duper String into a superduper_string
	{
		return strtolower(str_replace(" ","_",preg_replace('/[^a-z0-9 ]/i',"",$title_string)));
	}
	public function saveBlog()
	{
		if (isset($_POST['password']) && $_POST['password'] == $this->blog_password)
		{
			if (!isset($_GET['post'])) // create new post
			{
				$this->posts[time()] = array("Title" => $_POST['title'], "Essay" => $_POST['essay']);
			}
			elseif (isset($this->posts[$_GET['post']]) && isset($_POST['delete'])) // delete a post
			{
				unset($this->posts[$_GET['post']]);
			}
			elseif (isset($this->posts[$_GET['post']])) // edit a post
			{
				$this->posts[$_GET['post']] = array("Title" => $_POST['title'], "Essay" => $_POST['essay']);
			}
			krsort($this->posts); // Sort the posts in reverse chronological order
			file_put_contents("posts.php", "<?php \$posts= ".var_export($this->posts, true) . "?>");
		}
	}
	public function displayEditor ()
	{
		$invalid = (isset($_POST['password']) && $_POST['password'] != $this->blog_password ? ' <span style="color:red;">Invalid Password</span>' : "");
		$title_value = ""; $essay_value = ""; $delete_button = "";
		if (isset($_GET['post']) && isset($this->posts[$_GET['post']]))
		{
			$title_value = $this->posts[$_GET['post']]['Title'];
			$essay_value = $this->posts[$_GET['post']]['Essay'];
			$delete_button = "<input type=\"submit\" value=\"Delete\" name=\"delete\" onclick=\"return confirm('DELETE. Are you sure?');\">";
		}
		$content = <<<LONG
		<form method="post" action="">
		<table>
		<tr><td>Title</td><td><input type="text" name="title" size="25" value="$title_value"></td></tr>
		<tr><td>Content</td><td><textarea name="essay" rows="30" cols="80">$essay_value</textarea></td></tr>
		<tr><td>Password</td><td><input type="password" name="password">$invalid</td></tr>
		<tr><td></td><td><input type="submit" value="Save">$delete_button</td></tr></table>
		</form>
		Edit a Post:<br>
LONG;
		if (!is_writable("posts.php"))
		{
			$content = "<span style=\"color:red;\">WARNING! posts.php not writeable</span>".$content;
		}
		foreach ($this->posts as $key => $array)
		{
			$content .= "<a href=\"write?post=".$key."\">{$array['Title']}</a><br>";
		}
		$this->displayPage("Editor","Edit your blog",$content);
	}
	public function controller() // There are 3 pages: Editor, Post, Homepage
	{
		if (isset($_GET['r']) && $_GET['r'] == "/write") // Editor
		{
			$this->saveBlog();
			$this->displayEditor();
		}
		elseif (isset($_GET['r']) && isset($this->titles[substr($_GET['r'],1)]) ) // Post
		{
			$post = $this->posts[$this->titles[substr($_GET['r'],1)]];
			$this->displayPage($post['Title'],substr($post['Essay'],0,100),
			"<h1>{$post['Title']}</h1><div>".nl2br($post['Essay'])."<br><br>Posted ".date("m/d/Y")."</div>");
		}
		else { // Homepage
			$all_posts = ""; // Might want to limit it to most recent 5 or so posts.
			foreach ($this->posts as $post)
			{
				$all_posts .= "<h1><a href=\"".$this->prettyUrl($post['Title'])."\">{$post['Title']}</a></h1><div>".nl2br($post['Essay'])."<br><br>Posted ".date("m/d/Y")."</div><br><br>";
			}
			$this->displayPage($this->blog_title, $this->blog_description,
			$all_posts); 
		}
	}
	public function displayPage($title, $description, $body)
	{
		?>
			<html>
			<head>
			<style type="text/css">
			body {font-family: Georgia; color: #888888;}
			h1 {margin-top: 0px;}
			#content {float: left;width: 80%;}
			#sidebar {float: right;}
			</style>
			<title><?php echo $title;?></title>
			<meta name="description" content="<?php echo $description;?>">
			</head>
			<body>
			<div id="content">
				<?php echo $body; ?>
			</div>
			<div id="sidebar">
				<a href="/" style="text-decoration:none;"><?php echo $this->blog_title;?></a><br><br>
				<?php 
					foreach ($this->posts as $post)
					{
						?><a href="/<?php echo $this->prettyUrl($post['Title']);?>">
						<?php echo $post['Title'];?></a><br><?php
					}
				?>
				<br><a href="/write" rel="nofollow">Admin</a><br>
			</div>
			</body>
			</html>
		<?php
	}
}
$blog = new Blog;
$blog->controller();
?>