<?php
namespace Krga\CustomerNotes\Model;

use Magento\Framework\Model\AbstractModel;
use Magento\Framework\DataObject\IdentityInterface;
use Krga\CustomerNotes\Helper\Config;
use Krga\CustomerNotes\Model\ResourceModel\Note as NoteResource;
use Krga\CustomerNotes\Model\HistoryFactory;
use Krga\CustomerNotes\Model\ResourceModel\History as HistoryResource;
use Krga\CustomerNotes\Model\ResourceModel\History\CollectionFactory as HistoryCollectionFactory;
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
    protected $configHelper;
    protected $historyFactory;
    protected $historyResource;
    protected $historyCollectionFactory;
    protected $tagFactory;
    protected $tagResource;
    protected $tagRelationCollectionFactory;
    protected $urlBuilder;

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        Config $configHelper,
        HistoryFactory $historyFactory,
        HistoryResource $historyResource,
        HistoryCollectionFactory $historyCollectionFactory,
        TagFactory $tagFactory,
        TagResource $tagResource,
        TagRelationCollectionFactory $tagRelationCollectionFactory,
        UrlInterface $urlBuilder,
        \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
        $this->configHelper = $configHelper;
        $this->historyFactory = $historyFactory;
        $this->historyResource = $historyResource;
        $this->historyCollectionFactory = $historyCollectionFactory;
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

    public function getHistoriesPerNoteLimit() {
        return $this->configHelper->getHistoriesPerNoteLimit();
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

    public function beforeSave()
    {
        $limitPerNote = $this->getHistoriesPerNoteLimit();
        $noteId = $this->getId();

        if ($noteId && $this->hasDataChanges() && $this->dataHasChangedFor('note')) {
            $history = $this->historyFactory->create();
            $history->setData([
                'note_id'       => $noteId,
                'customer_id'   => $this->getCustomerId(),
                'previous_note' => $this->getOrigData('note'),
                'modified_at'   => date('Y-m-d H:i:s'),
            ]);
            $this->historyResource->save($history);

            $historyCollection = $this->historyCollectionFactory->create()
                ->addFieldToFilter('note_id', ['eq' => $noteId])
                ->setOrder('modified_at', 'ASC');

            if ($limitPerNote > 0 && $historyCollection->getSize() > $limitPerNote) {
                $historyToDelete = $historyCollection
                    ->setPageSize($historyCollection->getSize() - $limitPerNote)
                    ->setCurPage(1)
                    ->getItems();

                foreach ($historyToDelete as $historyItem) {
                    $this->historyResource->delete($historyItem);
                }
            }
        }

        return parent::beforeSave();
    }
}
