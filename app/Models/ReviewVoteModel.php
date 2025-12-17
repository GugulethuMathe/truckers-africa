<?php

namespace App\Models;

use CodeIgniter\Model;

class ReviewVoteModel extends Model
{
    protected $table = 'review_votes';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'review_id',
        'truck_driver_id',
        'vote_type'
    ];

    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = null;

    protected $validationRules = [
        'review_id' => 'required|integer',
        'truck_driver_id' => 'required|integer',
        'vote_type' => 'required|in_list[helpful,not_helpful]'
    ];

    protected $validationMessages = [];
    protected $skipValidation = false;
    protected $cleanValidationRules = true;

    // Custom methods
    public function getReviewVotes($reviewId)
    {
        return $this->where('review_id', $reviewId)->findAll();
    }

    public function getVotesByDriver($driverId)
    {
        return $this->where('truck_driver_id', $driverId)
                    ->orderBy('created_at', 'DESC')
                    ->findAll();
    }

    public function hasDriverVoted($reviewId, $driverId)
    {
        return $this->where('review_id', $reviewId)
                    ->where('truck_driver_id', $driverId)
                    ->first() !== null;
    }

    public function getDriverVote($reviewId, $driverId)
    {
        return $this->where('review_id', $reviewId)
                    ->where('truck_driver_id', $driverId)
                    ->first();
    }

    public function castVote($reviewId, $driverId, $voteType)
    {
        // Check if driver has already voted
        $existingVote = $this->getDriverVote($reviewId, $driverId);
        
        if ($existingVote) {
            // Update existing vote if different
            if ($existingVote['vote_type'] !== $voteType) {
                return $this->update($existingVote['id'], ['vote_type' => $voteType]);
            }
            return true; // Same vote, no change needed
        } else {
            // Create new vote
            return $this->insert([
                'review_id' => $reviewId,
                'truck_driver_id' => $driverId,
                'vote_type' => $voteType
            ]);
        }
    }

    public function removeVote($reviewId, $driverId)
    {
        return $this->where('review_id', $reviewId)
                    ->where('truck_driver_id', $driverId)
                    ->delete();
    }

    public function getVoteCounts($reviewId)
    {
        $helpful = $this->where('review_id', $reviewId)
                        ->where('vote_type', 'helpful')
                        ->countAllResults();
        
        $notHelpful = $this->where('review_id', $reviewId)
                           ->where('vote_type', 'not_helpful')
                           ->countAllResults();
        
        return [
            'helpful' => $helpful,
            'not_helpful' => $notHelpful,
            'total' => $helpful + $notHelpful
        ];
    }

    public function getMostHelpfulReviews($limit = 10)
    {
        return $this->select('review_id, COUNT(*) as helpful_votes')
                    ->where('vote_type', 'helpful')
                    ->groupBy('review_id')
                    ->orderBy('helpful_votes', 'DESC')
                    ->limit($limit)
                    ->findAll();
    }
}
