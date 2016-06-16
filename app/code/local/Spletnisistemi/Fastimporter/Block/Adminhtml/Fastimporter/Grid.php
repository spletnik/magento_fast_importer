<?php

class Spletnisistemi_Fastimporter_Block_Adminhtml_Fastimporter_Grid extends Mage_Adminhtml_Block_Widget_Grid {
    public function __construct() {
        parent::__construct();
        $this->setId('fastimporterGrid');
        $this->setDefaultSort('profile_id');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
    }

    public function getRowUrl($row) {
        return $this->getUrl('*/*/edit', array('id' => $row->getId()));
    }

    protected function _prepareCollection() {
        $collection = Mage::getModel('fastimporter/fastimporter')->getCollection();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns() {
        $this->addColumn('profile_id', array(
            'header' => Mage::helper('fastimporter')->__('ID'),
            'align'  => 'right',
            'width'  => '50px',
            'index'  => 'profile_id',
        ));

        $this->addColumn('profile_name', array(
            'header' => Mage::helper('fastimporter')->__('Profile name'),
            'align'  => 'left',
            'index'  => 'profile_name',
        ));

        $this->addColumn('filename', array(
            'header'   => Mage::helper('fastimporter')->__('CSV file'),
            'align'    => 'left',
            'index'    => 'filename',
            'renderer' => new Spletnisistemi_Fastimporter_Block_Adminhtml_Renderer_Csvfile(),
        ));
        $this->addColumn('mode', array(
            'header'   => Mage::helper('fastimporter')->__('Import mode'),
            'width'    => '150px',
            'index'    => 'mode',
            'renderer' => new Spletnisistemi_Fastimporter_Block_Adminhtml_Renderer_Importmode(),
        ));
        $this->addColumn('cronjob', array(
            'header'   => Mage::helper('fastimporter')->__('Cronjob'),
            'width'    => '150px',
            'index'    => 'cronjob',
            'renderer' => new Spletnisistemi_Fastimporter_Block_Adminhtml_Renderer_Cronjob(),
        ));
        $this->addColumn('cronjob_executed', array(
            'header' => Mage::helper('fastimporter')->__('Cronjob Executed'),
            'width'  => '150px',
            'index'  => 'cronjob_executed',
        ));
        $this->addColumn('status', array(
            'header'  => Mage::helper('fastimporter')->__('Status'),
            'align'   => 'left',
            'width'   => '80px',
            'index'   => 'status',
            'type'    => 'options',
            'options' => array(
                1 => 'Enabled',
                2 => 'Disabled',
            ),
        ));

        $this->addColumn('action',
            array(
                'header'    => Mage::helper('fastimporter')->__('Action'),
                'width'     => '100',
                'type'      => 'action',
                'getter'    => 'getId',
                'actions'   => array(
                    array(
                        'caption' => Mage::helper('fastimporter')->__('Edit'),
                        'url'     => array('base' => '*/*/edit'),
                        'field'   => 'id'
                    )
                ),
                'filter'    => false,
                'sortable'  => false,
                'index'     => 'stores',
                'is_system' => true,
            ));

        //$this->addExportType('*/*/exportCsv', Mage::helper('fastimporter')->__('CSV'));
        //$this->addExportType('*/*/exportXml', Mage::helper('fastimporter')->__('XML'));

        return parent::_prepareColumns();
    }

    protected function _prepareMassaction() {
        $this->setMassactionIdField('profile_id');
        $this->getMassactionBlock()->setFormFieldName('fastimporter');

        $this->getMassactionBlock()->addItem('delete', array(
            'label'   => Mage::helper('fastimporter')->__('Delete'),
            'url'     => $this->getUrl('*/*/massDelete'),
            'confirm' => Mage::helper('fastimporter')->__('Are you sure?')
        ));

        $statuses = Mage::getSingleton('fastimporter/status')->getOptionArray();

        array_unshift($statuses, array('label' => '', 'value' => ''));
        $this->getMassactionBlock()->addItem('status', array(
            'label'      => Mage::helper('fastimporter')->__('Change status'),
            'url'        => $this->getUrl('*/*/massStatus', array('_current' => true)),
            'additional' => array(
                'visibility' => array(
                    'name'   => 'status',
                    'type'   => 'select',
                    'class'  => 'required-entry',
                    'label'  => Mage::helper('fastimporter')->__('Status'),
                    'values' => $statuses
                )
            )
        ));
        return $this;
    }

}