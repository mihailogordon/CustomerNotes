<?php
namespace Krga\CustomerNotes\Model;

use Magento\Framework\Model\AbstractModel;
use Magento\Framework\DataObject\IdentityInterface;
use Krga\CustomerNotes\Model\ResourceModel\Note as NoteResource;
use Krga\CustomerNotes\Model\HistoryFactory;
use Krga\CustomerNotes\Model\ResourceModel\History as HistoryResource;
use Krga\CustomerNotes\Model\TagFactory;
use Krga\CustomerNotes\Model\ResourceModel\Tag as TagResource;
use Krga\CustomerNotes\Model\ResourceModel\TagRelation\CollectionFactory as TagRelationCollectionFactory;
use Magento\Framework\UrlInterface;

class Note extends AbstractModel implements IdentityInterface
{
    const CACHE_TAG = 'krga_customernotes_note';
    const TAGS_PATH = 'notes/tags/index';

    protected $_cacheTag = self::CACHE_TAG;
    protected $_eventPrefix = 'note';
    protected $historyFactory;
    protected $historyResource;
    protected $tagFactory;
    protected $tagResource;
    protected $tagRelationCollectionFactory;
    protected $urlBuilder;

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        HistoryFactory $historyFactory,
        HistoryResource $historyResource,
        TagFactory $tagFactory,
        TagResource $tagResource,
        TagRelationCollectionFactory $tagRelationCollectionFactory,
        UrlInterface $urlBuilder,
        \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
        $this->historyFactory = $historyFactory;
        $this->historyResource = $historyResource;
        $this->tagFactory = $tagFactory;
        $this->tagResource = $tagResource;
        $this->tagRelationCollectionFactory = $tagRelationCollectionFactory;
        $this->urlBuilder = $urlBuilder;
    }

    protected function _construct()
    {
        $this->_init(NoteResource::class);
    }

    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    public function getNoteCustomer($customerId)
    {
        try {
            $customerRepository = \Magento\Framework\App\ObjectManager::getInstance()
                ->get(\Magento\Customer\Api\CustomerRepositoryInterface::class);

            $customer = $customerRepository->getById($customerId);

            return [
                'id' => $customer->getId(),
                'email' => $customer->getEmail(),
                'firstname' => $customer->getFirstname(),
                'lastname' => $customer->getLastname()
            ];
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            return null;
        }
    }

    public function getNoteTagsHtml() {
        $noteId = $this->getNoteId();
        $output = '';
        $outputItems = array();

        if($noteId) {
            $tags = $this->tagRelationCollectionFactory->create()->addFieldToFilter('note_id', array('eq' => $noteId))->getItems();

            if (is_array($tags) && count($tags) > 0) {
                $output .= '<span class="yemora-tags">Tagged as: ';
                
                foreach ($tags as $tag) {
                    $tagId = $tag->getTagId();
                    $tagObject = $this->tagFactory->create();
                    $this->tagResource->load($tagObject, $tagId);
                    $outputItems[] = '<a class="yemora-tag" href="' . $this->urlBuilder->getUrl(self::TAGS_PATH, ['tag_id' => $tagId]) . '">' . $tagObject->getName() . '</a>';
                }

                $output .= implode(', ', $outputItems);
                $output .= '</span>';
            }
        }

        return $output;
    }

    /**
     * Before saving a note, store the previous version in history.
     */
    public function beforeSave()
    {
        if ($this->getId() && $this->hasDataChanges() && $this->dataHasChangedFor('note')) {
            // Store the previous note version before updating
            $history = $this->historyFactory->create();
            $history->setData([
                'note_id'       => $this->getId(),
                'customer_id'   => $this->getCustomerId(),
                'previous_note' => $this->getOrigData('note'), // Fetch old note content
                'modified_at'   => date('Y-m-d H:i:s'),
            ]);

            // Save the history record
            $this->historyResource->save($history);
        }

        return parent::beforeSave();
    }
}
