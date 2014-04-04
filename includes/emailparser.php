<?php

class EmailPart {
	const PART_BODY = 1;
	const PART_REPLY = 2;
	const PART_SIG = 3;


	public $type = EmailPart::PART_BODY;
	public $lines = [];

	public function __construct($type) {
		$this->type = $type;
	}

	public function has_lines() {
		return count($this->lines) > 0;
	}

	public function add_line($line) {
		$this->lines[] = $line;
	}
}

class Email {

	const STATE_ANY = 0;
	const STATE_BODY = 1;
	const STATE_REPLY = 2;
	const STATE_SIG = 3;
	const STATE_REPLY_INDENT = 4;
	const STATE_REPLY_PERPETUAL = 5;

	protected $parts = [];
	protected $segments = [
		['#^On .+\d{4}.+ wrote:\s*$#i', [Email::STATE_BODY, Email::STATE_SIG], Email::STATE_REPLY, EmailPart::PART_REPLY],
		['#^Am .+\d{4}.+ schrieb.*:\s*$#i', [Email::STATE_BODY, Email::STATE_SIG], Email::STATE_REPLY, EmailPart::PART_REPLY],
		['#^\s*-+\s*Original Message\s*-+\s*$#i', [], Email::STATE_REPLY_PERPETUAL, EmailPart::PART_REPLY],
		['#^\s*Date:.+ \d{4} .+#i', [Email::STATE_BODY], Email::STATE_REPLY_PERPETUAL, EmailPart::PART_REPLY],
		['#^\s*From:.+@.+#i', [Email::STATE_BODY], Email::STATE_REPLY_PERPETUAL, EmailPart::PART_REPLY],
		['#^\s*>#i', [Email::STATE_BODY, Email::STATE_REPLY], Email::STATE_REPLY_INDENT, EmailPart::PART_REPLY],
		['#^\s*[^>]#i', [Email::STATE_REPLY_INDENT], Email::STATE_BODY, EmailPart::PART_BODY],
		['#^Sent from my .+$#i', [Email::STATE_BODY, Email::STATE_REPLY, Email::STATE_REPLY_INDENT], Email::STATE_SIG, EmailPart::PART_SIG],
		['#^[-_]+\s*$#i', [Email::STATE_BODY, Email::STATE_REPLY, Email::STATE_REPLY_INDENT], Email::STATE_SIG, EmailPart::PART_SIG],
	];
	protected $parsed = false;

	public function __construct($body) {
		$this->body = $body;
	}

	public function parse() {
		$lines = preg_split('#(\r\n|\n|\r)#', $this->body);
		$current_state = Email::STATE_BODY;
		$save_segment = Email::STATE_BODY;
		$this->parts = [];
		$part = new EmailPart(EmailPart::PART_BODY);
		foreach ($lines as $line) {
			$new_state = $current_state;
			foreach ($this->segments as $segment) {
				if(count($segment[1]) > 0 && !in_array($current_state, $segment[1])) {
					continue;
				}
				if(preg_match($segment[0], $line)) {
					$new_state = $segment[2];
					$save_segment = $segment[3];
					break;
				}
			}
			if($new_state != $current_state) {
				if($part->has_lines() && $part->type != $save_segment) {
					$this->parts[] = $part;
					$part = new EmailPart($save_segment);
				}
				elseif(!$part->has_lines()) {
					$part = new EmailPart($save_segment);
				}
				$current_state = $new_state;
			}
			$part->add_line($line);
		}
		if($part->has_lines()) {
			$this->parts[] = $part;
		}
		$this->parsed = true;
		return $this->parts;
	}

	public function render() {
		if(!$this->parsed) {
			$this->parse();
		}
		$output = '';
		foreach($this->parts as $part) {
			$lines = implode("\r\n<br>", $part->lines);
			$output .= '<div class="email_part email_part_';
			switch($part->type) {
				case EmailPart::PART_BODY:
					$output .= 'body';
					break;
				case EmailPart::PART_REPLY:
					$output .= 'reply';
					break;
				case EmailPart::PART_SIG:
					$output .= 'sig';
					break;
			}
			$output .= '"><span class="email_collapsed">&hellip;</span><span class="email_content">' . $lines . '</span></div>';
		}
		return $output;
	}
}