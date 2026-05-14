<?php

namespace Aztec;

use Aztec\Encoder\Encoder;
use Com\Tecnick\Color;

class Aztec
{
	private $options = [];
	private $renderer;

	public function __construct(array $opts = [])
	{
		if (!isset($opts['hint'])) {
			$this->options['hint'] = "dynamic";
		} else {
			if (!in_array($opts['hint'], ["binary", "dynamic"])){
				throw azException::InvalidInput("Invalid value for \"hint\". Expected \"binary\" or \"dynamic\".");
			}
			$this->options['hint'] = $opts['hint'];
		}

                if (!isset($opts['Color'])) {
                        $this->setColor('Color', 'black');
                    } else {
                        $this->setColor('Color', $opts['Color']);
                }
                if (isset($opts['bgColor'])) {
                        $this->setColor('bgColor', $opts['bgColor']);
                }
                if (isset($opts['fsColor'])) {
                        $this->setColor('fsColor', $opts['fsColor']);
                }
                if (isset($opts['bdColor'])) {
                        $this->setColor('bdColor', $opts['bdColor']);
                }

		$this->options['ratio'] = (isset($opts['ratio'])) ? $this->option_in_range($opts['ratio'], 1, 10) : 4;
		$this->options['padding'] = (isset($opts['padding'])) ? $this->option_in_range($opts['padding'], 0, 50) : 20;
		$this->options['quality'] = (isset($opts['quality'])) ? $this->option_in_range($opts['quality'], 0, 100) : 100;
		$this->options['eccPercent'] = (isset($opts['eccPercent'])) ? $this->option_in_range($opts['eccPercent'], 1, 100) : 33;
	}

        private function setColor($value, $color)
        {
                $this->options[$value] = $this->getRgbColorObject($color);
                if ($this->options[$value] === null) {
                    echo 'there is no object';
//                  throw new BarcodeException('The foreground color cannot be empty or transparent');
//                  throw azException::InvalidInput("Invalid value for \"$value\". Expected an azColor object.");
                }
//              return $this;
        }

        protected function getRgbColorObject($color)
        {
            $conv = new \Com\Tecnick\Color\Pdf();
            $cobj = $conv->getColorObject($color);
            if ($cobj !== null) {
                return new \Com\Tecnick\Color\Model\Rgb($cobj->toRgbArray());
            }
            return null;
        }

	public function config(array $opts)
	{
		$this->__construct($opts);
	}

	private function option_in_range($value, int $start, int $end)
	{
		if (!is_numeric($value) || $value < $start || $value > $end) {
			throw azException::InvalidInput("Invalid value. Expected an integer between $start and $end.");
		}

		return $value;
	}

	public function toFile(string $filename, bool $forWeb = false)
	{
		$ext = strtoupper(substr($filename, -3));
		($forWeb) AND $filename = null;

		switch($ext)
		{
			case "PNG":
				$this->renderer->toPNG($filename);
				break;
			case "GIF":
				$this->renderer->toGIF($filename);
				break;
			case "JPG":
				$this->renderer->toJPG($filename, $this->options['quality']);
				break;
			default:
				throw azException::InvalidInput('File extension unsupported!');
		}
	}

	public function forWeb(string $ext)
	{
		if (strtoupper($ext) == "BASE64"){
			return $this->renderer->toBase64();
		} else {
			$this->toFile($ext, true);
		}
	}

	public function forPChart(\pChart\pDraw $MyPicture, $X = 0, $Y = 0)
	{
		$this->renderer->forPChart($MyPicture->gettheImage(), $X, $Y);
	}

	public function encode($data)
	{
		$pixelGrid = (new Encoder())->encode($data, $this->options['eccPercent'], $this->options["hint"]);

		$this->renderer = new Renderer($pixelGrid, $this->options);

		return $this;
	}
}
