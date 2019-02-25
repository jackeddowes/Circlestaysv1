<?php















namespace Drone;

if (defined('\Drone\VERSION')) {
	return \Drone\VERSION;
}






const VERSION = '5.8.2';






const DIRECTORY = 'drone';












function apply_filters($tag, $value)
{
	$args = array_slice(func_get_args(), 2);
	$value = call_user_func_array('\apply_filters', array_merge(['drone_' . $tag, $value], $args));
	$value = call_user_func_array('\apply_filters', array_merge([Theme::instance()->base_theme->id_ . '_' . $tag, $value], $args));
	return $value;
}










function do_action($tag)
{
	$args = array_slice(func_get_args(), 1);
	call_user_func_array('\do_action', array_merge(['drone_' . $tag], $args));
	call_user_func_array('\do_action', array_merge([Theme::instance()->base_theme->id_ . '_' . $tag], $args));
}




$drone_dir = get_template_directory() . '/' . DIRECTORY;

spl_autoload_register(function ($class) use ($drone_dir) {

	if (preg_match('/^' . __NAMESPACE__ . '\\\\([A-Z][A-Za-z]*)\b/', $class, $m)) {
		require_once $drone_dir . '/' . strtolower($m[1]) . '.php';
	}

});

return \Drone\VERSION;