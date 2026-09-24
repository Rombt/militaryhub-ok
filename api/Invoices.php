<?php

require_once('Okay.php');

class Invoices extends Okay {
    public function add_order_invoice ($data) {
        $invoice = (object) $data;

        $query = $this->db->placehold("
            INSERT
            INTO __order_invoices
            SET ?%
        ", $invoice);

        $this->db->query($query);

        return $this->db->insert_id();
    }
    public function get_order_invoices ($filter = []) {
        $where = [];

        if ($filter['id']) {
            $where[] = $this->db->placehold("id = ?", $filter['id']);
        }
        if ($filter['order_id']) {
            $where[] = $this->db->placehold("order_id = ?", $filter['order_id']);
        }
        if ($filter['order_invoice_id']) {
            $where[] = $this->db->placehold("order_invoice_id = ?", $filter['order_invoice_id']);
        }
        if ($filter['payment_method']) {
            $where[] = $this->db->placehold("payment_method = ?", $filter['payment_method']);
        }
        if ($filter['payment_method_id']) {
            $where[] = $this->db->placehold("payment_method_id = ?", $filter['payment_method_id']);
        }
        if ($filter['status']) {
            $where[] = $this->db->placehold("status = ?", $filter['status']);
        }

        if(empty($where)){
            $where = '';
        } else {
            $where = implode(' AND ', $where);
        }

        $query = $this->db->placehold("
            SELECT *
            FROM __order_invoices
            WHERE $where
        ");
        $this->db->query($query);

        return $this->db->results();
    }
    public function get_order_invoice ($filter = []) {
        if (empty($filter)) {
            return false;
        }
        $where = [];

        if (!empty($filter['id'])) {
            $where[] = $this->db->placehold("id = ?", $filter['id']);
        }
        if (!empty($filter['order_id'])) {
            $where[] = $this->db->placehold("order_id = ?", $filter['order_id']);
        }
        if (!empty($filter['order_invoice_id'])) {
            $where[] = $this->db->placehold("order_invoice_id = ?", $filter['order_invoice_id']);
        }
        if (!empty($filter['status'])) {
            $where[] = $this->db->placehold("status = ?", $filter['status']);
        }

        $where = implode(' AND ', $where);

        $query = $this->db->placehold("
            SELECT *
            FROM __order_invoices
            WHERE $where
            ORDER BY id DESC
            LIMIT 1
        ");
        $this->db->query($query);

        return $this->db->result();
    }

    public function update_order_invoice ($id, $id_key = 'id', $data) {
        if(empty($id) || !in_array($id_key, ['id', 'order_invoice_id'])){
            return false;
        }
        $invoice = (object) $data;

        $query = $this->db->placehold("
            UPDATE __order_invoices
            SET ?%
            WHERE $id_key = ?
        ", $invoice, $id);
        $this->db->query($query);
    }

    /* A more reliable option for creating invoices on this site than now. */
    public function create_invoice ($data) {
        if (
            empty($data)
            || empty($data['order_id'])
            || empty($data['payment_method'])
            || empty($data['payment_method_id'])
        ) {
            return false;
        }

        $invoice_order = $this->orders->get_order((int)$data['order_id']);
        $old_invoice = $this->get_order_invoice([
            'order_id' => $data['order_id'],
            'status' => 'pending'
        ]);
        if (!empty($old_invoice)) {
            $this->cancel_invoice($old_invoice->id);
        }
        $invoice = new stdClass;
        $invoice->order_id = $data['order_id'];
        $invoice->payment_method =  preg_replace("/[^A-Za-z0-9]+/", "", $data['payment_method']);
        $invoice->payment_method_id = $data['payment_method_id'];
        $invoice->payment_type = $data['payment_type'];

        if (
            !empty($data['payment_type'])
            && $data['payment_type'] == 'advance'
            && $invoice_order->total_advance > PHP_FLOAT_EPSILON
            && !$invoice_order->advance_paid
        ) {
            $invoice->payment = $invoice_order->total_advance;
        } else {
            $invoice->payment = $invoice_order->total_price;
            if (!empty($invoice_order->advance_paid)) {
                $invoice->payment -= $invoice_order->total_advance;
            }
        }
        $invoice->order_invoice_id = 'INV_' . $invoice_order->id. '_' . date('YmdHis');

        $invoice_id = $this->add_order_invoice($invoice);
        if (!empty($invoice_id)) {
            include_once("payment/$invoice->payment_method/$invoice->payment_method.php");
            $payment_module = new $invoice->payment_method();
            $payment_module->create_invoice($invoice_id);
            return true;
        }

        return false;
    }

    public function cancel_invoice ($id) {
        if (empty($id)) {
            return false;
        }
        $invoice = $this->get_order_invoice(['id' => $id]);
        if ($invoice->status == 'pending') {
            include_once("payment/$invoice->payment_method/$invoice->payment_method.php");
            $payment_module = new $invoice->payment_method();
            $payment_module->cancel_invoice($id);

            return true;
        }
        return false;
    }
}

