<?php

/**
 * Simple template for tipi
 *
 * @author reeze <reeze.xia@gmail.com>
 */
class SimpieView
{
	protected $layout;
	protected $view;

	public function __construct($view, $layout=null)
	{
		$this->view   = $view;
		$this->layout = $layout;
	}

	/**
	 * Render the view/view with layout
	 *
	 * @param array $params the params to extract to template
	 * @param bool  $return is return or direct output the template
	 *
	 * @return mixed template's output or null
	 */
	public function render($params=array(), $return=false)
	{
		$view_result = $this->_render($this->view, $params, $this->layout || $return);
		if($this->layout) {
			$params['layout_content'] = $view_result;
			return $this->_render($this->layout, $params, $return);
		}

		return $view_result;
	}

	/**
	 * Act likes include and Simpie's render method. used in template file to include partial
	 *
	 * @param string $partial_path 	the partial to include
	 * @param array  $params 		the param to extract
	 *
	 * @return mixed template's output or null
	 */
	public static function include_partial($partial_path, $params, $return=false) {
		$partial = new self($partial);

		return $partial->render($params, $return);
	}

	private function _render($view_path, $params=array(), $return=false) 
	{
		if(!file_exists($view_path)) {
			throw new SimpieViewNotFoundException("View path:{$view_path} not found");	
		}

		$file_type = substr($view_path, strrpos($view_path, '.') + 1);
		$file_type = in_array($file_type, array('php', 'markdown')) ? $file_type : 'php';

		return call_user_func(array($this, "_render_{$file_type}"), $view_path, $params, $return);
	}

	private function _render_php($view_path, $params=array(), $return=false)
	{
		if($return) ob_start();
		// Simple extract
		extract($params);
		require $view_path;

		if($return) {
			return ob_get_clean();	
		}
	}

	private function _render_markdown($view_path, $params=array(), $return=false)
	{
		require_once "MarkdownExtraGeshiSyntax/markdown_geshi/markdown_geshi.php";
		
		$output = Markdown(file_get_contents($view_path));

		if($return)	{
			return $output;
		}
		else {
			echo $output;	
		}
	}
}

class SimpieViewNotFoundException extends Exception {}