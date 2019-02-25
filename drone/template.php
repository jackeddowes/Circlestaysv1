<?php








namespace Drone;






class Template
{







	const P_ESC = '(?<!\\\\)\\\\';







	const P_NOT_ESC = '(?<!(?<!\\\\)\\\\)';







	const P_VAR = '(?<!\[if):(?|\{(?P<var>[_a-z0-9]+)(?P<func>(\.[_a-z0-9]+)*)\}|(?P<var>[_a-z0-9]+)(?P<func>(\.[_a-z0-9]+)*))';







	const P_BLOCK = '\[(?:if[^\]]+|else|endif)\]';







	const P_BLOCK_COND = ':(?P<not>!?)(?P<var>[_a-z0-9]+)';







	const P_BLOCK_BODY = '(?:(?>[^\[]*)|(?R)|.)*';







	protected $vars = [];







	public $body = '';









	public function __get($name)
	{
		return $this->get($name);
	}









	public function __set($name, $value)
	{
		$this->set($name, $value);
	}










	public function __call($name, $args)
	{
		return $this->set($name, count($args) > 0 ? $args[0] : true);
	}








	public function __toString()
	{
		return $this->build();
	}









	public function body($body)
	{
		$this->body = $body;
		return $this;
	}









	public function get($name)
	{
		$name = strtolower((string)$name);
		return isset($this->vars[$name]) ? $this->vars[$name] : null;
	}









	public function getValue($name)
	{

		if (($var = $this->get($name)) === null && ($var = $this->get('default')) === null) {
			return;
		}

		if ($var instanceof \Closure || (is_array($var) && is_callable($var))) {
			return call_user_func($var, $name);
		}

		if (is_object($var)) {
			return $var instanceof self ? $var->build() : get_object_vars($var);
		}

		return $var;

	}










	public function set($name, $value = true)
	{

		if (is_array($name)) {

			foreach ($name as $key => $value) {
				$this->set($key, $value);
			}

		} else {

			$name = strtolower((string)$name);

			if ($value === null) {
				unset($this->vars[$name]);
			} else {
				$this->vars[$name] = $value;
			}

		}

		return $this;

	}









	protected function parseBlocks($s)
	{

		$pattern =
			self::P_NOT_ESC . '\[if' . self::P_BLOCK_COND . '\]' .
				'(?P<true>' . self::P_BLOCK_BODY . ')' .
				'(?|' . self::P_NOT_ESC . '\[else\](?P<false>' . self::P_BLOCK_BODY . ')|(?P<false>))?' .
			self::P_NOT_ESC . '\[endif\]';

		do {

			$s = preg_replace_callback('/' . $pattern . '/s', function ($m) {

				return ( (bool)$m['not'] xor (bool)$this->getValue($m['var']) ) ? $m['true'] : $m['false'];

			}, $s, -1, $count);

		} while ($count > 0);

		return $s;

	}









	protected function parseVars($s)
	{

		return preg_replace_callback('/' . self::P_NOT_ESC . self::P_VAR . '/', function ($m) {

			$value = $this->getValue($m['var']);


			if ($m['func']) {
				foreach (explode('.', substr($m['func'], 1)) as $func) {
					if (is_callable($func)) {
						$value = call_user_func($func, $value);
					}
				}
			}


			switch (gettype($value)) {
				case 'boolean': return $value ? 'true' : 'false';
				case 'array':   return esc_attr(json_encode($value));
				default:        return (string)$value;
			}

		}, $s);

	}








	public function build()
	{

		$protocols = \apply_filters('kses_allowed_protocols', wp_allowed_protocols());


		$output = $this->body;


		$output = $this->parseBlocks($output);


		$output = preg_replace_callback('/(?<=\s)(?P<attr>[-a-z0-9]+)=(?|(?P<q>")(?P<val>[^"]+)"|(?P<q>\')(?P<val>[^\']+)\')/i', function ($m) use ($protocols) {

			if (preg_match('/' . Template::P_ESC . Template::P_VAR . '/', $m['val'])) {
				return $m[0];
			}

			$value = $this->parseVars($m['val']);
			$value = in_array($m['attr'], ['href', 'src', 'action', 'itemtype']) ? esc_url($value, $protocols) : esc_attr($value);

			return $m['attr'] . '=' . $m['q'] . Template::escape($value) . $m['q'];

		}, $output);

		$output = $this->parseVars($output);


		$output = self::unescape($output);

		return $output;

	}










	public static function instance($body = '', $vars = [])
	{

		if (!is_array($vars) || func_num_args() > 2) {
			$vars = array_slice(func_get_args(), 1);
		}

		$template = new self;

		return $template->body($body)->set($vars);

	}









	public static function escape($s)
	{
		return preg_replace('/' . self::P_NOT_ESC . '(' . self::P_BLOCK . '|' . self::P_VAR . ')/', '\\\\\0', $s);
	}









	public static function unescape($s)
	{
		return preg_replace('/' . self::P_ESC . '(?=' . self::P_BLOCK . '|' . self::P_VAR . ')/', '', $s);
	}

}