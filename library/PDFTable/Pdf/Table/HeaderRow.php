<?php
class PDFTable_Pdf_Table_HeaderRow extends PDFTable_Pdf_Table_Row {
	
	private $_align;
	private $_vAlign;
	
	public function setAlignment($align){
		$this->_align=$align;
	}
	public function setVAlignment($align){
		$this->_vAlign=$align;
	}
	
	public function __construct($labels=array()){
		
		$cols=null;
		foreach ($labels as $label) {
			$col=new PDFTable_Pdf_Table_Column();
			$col->setText($label);
			$cols[]=$col;
		}
		if($cols)
			$this->setColumns($cols);
		
		//set default alignment
		$this->_align=PDFTable_Pdf::LEFT;
		
		//set default borders
		$style=new Zend_Pdf_Style();
		$style->setLineWidth(1);
		$this->setBorder(PDFTable_Pdf::BOTTOM,$style);
		$this->setCellPaddings(array(1,1,1,1));
		
		//set default font
		$this->_font=Zend_Pdf_Font::fontWithName ( ZEND_Pdf_Font::FONT_HELVETICA_BOLD); 
		$this->_fontSize=8;
	}
	
	public function preRender(PDFTable_Pdf_Page $page,$posX,$posY){
		
		foreach ($this->_cols AS $col){
			//set default font
			if(!$col->getFont())
				$col->setFont($this->_font,$this->_fontSize);
			//set default borders if not set
			foreach ($this->_border as $pos=>$style) {
				if(!$col->getBorder($pos))
					$col->setBorder($pos,$style);
			}
			
			if(!$col->getAlignment())
				$col->setAlignment($this->_align);
		}
		
		parent::preRender($page,$posX,$posY);
	}
}
?>