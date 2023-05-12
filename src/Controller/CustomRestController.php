<?php

namespace App\Controller;

use Pimcore\Model\DataObject;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use \Pimcore\Controller\FrontendController;
use Pimcore\Model\DataObject\Data\ExternalImage;


class CustomRestController extends FrontendController
{
    /**
     * @Route("/user-login", name="user_login", methods={"POST"})
     */
    public function userLoginAction(Request $request){
        $userEmail = $request->get('email');
        $userPassword = $request->get('password');
        if($userEmail && $userPassword){
            $success = true;
            $msg = 'OK';
            $data = array(['email'=>$userEmail,'password'=>$userPassword]);
        }else{
            $success = false;
            $msg = 'email or password is required';
            $data = array();
        }
        return $this->json(["success" => $success, "msg" => $msg, 'data'=>$data]);
    }

    /**
     * @Route("/user-register", name="user_register", methods={"POST"})
     */
    public function userRegisterAction(Request $request){
        $userName = $request->get('name');
        $userEmail = $request->get('email');
        $userPassword = $request->get('password');
        $userCPassword = $request->get('cpassword');
        if($userEmail && $userPassword && $userName && $userCPassword){
            if($userPassword != $userCPassword){
                $success = false;
                $msg = 'password and cpassword should be same';
                $data = array(['name' => $userName,'email' => $userEmail,'password' => $userPassword,'cpassword' => $userCPassword]);
            }else{
                $success = true;
                $msg = 'OK';
                $data = array(['name' => $userName,'email' => $userEmail,'password' => $userPassword,'cpassword' => $userCPassword]);
            }
            
        }else{
            $success = false;
            $msg = 'All fields are required';
            $data = array();
        }
        return $this->json(["success" => $success, "msg" => $msg, 'data'=>$data]);
    }
    /**
     * @Route("/product-delete", name="product_delete", methods={"POST"})
     */
    public function productIdDeleteAction(Request $request)
    {
        try{
            $id = $request->get('id');
            $productList = new DataObject\Product\Listing();
            $productList->setCondition("oo_id LIKE ?", $id);
            $productDetails = $productList->load();
            
            if($productDetails){
                foreach ($productDetails as $key => $product) {
                    $product->delete();  
                }
                $msg = "Product Deleted Successfully";
                $success = true;
            }else{
                $msg = "Product id is invalid.";
                $success = false;
            }
        } catch (\Exception $e) {
            $msg = array($e->getMessage());
            $success = false;
        }
        return $this->json(["success" => $success, "msg" => $msg]);
    }
    /**
     * @Route("/product-create", name="product_create", methods={"POST"})
     */
    public function productCreateAction(Request $request)
    {
        try{
            $productDetails = new DataObject\Product();

            $productSKU = $request->get('productSKU');
            $productName = $request->get('productName');
            $productParentId = $request->get('productParentId');
            $productKey = 'product-'.$productSKU;

            $categories = $request->get('categories');
            $productCategories = array();
            $productCategory = explode(",", $categories);
            foreach($productCategory as $eachCategoryId) {
                $productCategories[] = DataObject\Category::getByCategoryId($eachCategoryId, 1);
            }

            $productImage = new ExternalImage();
            $productImage->setUrl($request->get('productImage'));
            

            $productDetails->setProductSKU($productSKU);
            $productDetails->setProductName($productName);
            $productDetails->setImage($productImage);
            $productDetails->setCategory($productCategories);
            $productDetails->setParentId($productParentId);
            $productDetails->setKey($productKey);
            $productDetails->setPublished(true);
            $productDetails->save();
            $msg = 'Product Created successfully!';
            $success = true;
            
        } catch (\Exception $e) {
            $msg = array($e->getMessage());
            $success = false;
        }
        return $this->json(["success" => $success, "msg" => $msg]);
    }

    /**
     * @Route("/product-update-by-id", name="product_id_update", methods={"POST"})
     */
    public function productIdUpdateAction(Request $request)
    {
        try{
            $id = $request->get('id');
            $productName = $request->get('productName');
            $productSKU = $request->get('productSKU');

            $categories = $request->get('categories');
            $productCategories = array();
            $productCategory = explode(",", $categories);
            foreach($productCategory as $eachCategoryId) {
                $productCategories[] = DataObject\Category::getByCategoryId($eachCategoryId, 1);
            }

            $productImage = new ExternalImage();
            $productImage->setUrl($request->get('productImage'));
            
            $productDetails = \Pimcore\Model\DataObject\Product::getById($id);
            
            if($productDetails){
                $productDetails->setProductSKU($productSKU);
                $productDetails->setProductName($productName);
                $productDetails->setImage($productImage);
                $productDetails->setCategory($productCategories);
                $productDetails->save();
                $msg = 'Product Updated successfully!';
                $success = true;
            }else{
                $msg = 'Id is invalid!';
                $success = true;
            }
        } catch (\Exception $e) {
            $msg = array($e->getMessage());
            $success = false;
        }
        return $this->json(["success" => $success, "msg" => $msg]);
    }

    /**
     * @Route("/product-view-by-id", name="product_id_view", methods={"POST"})
     */
    public function productIdViewAction(Request $request)
    {
        try{
            $id = $request->get('id');
            $productList = new DataObject\Product\Listing();

            $productList->setCondition("oo_id LIKE ?", $id);
            $productDetails = $productList->load();
            
            if($productDetails){
                foreach ($productDetails as $key => $product) {
                    $categories = array();
                    foreach ($product->getCategory() as $key => $eachCategory) {
                       $categories[$eachCategory->getCategoryId()] = $eachCategory->getCategoryName() ;
                    }
                    $data[] = array(
                        "productSKU" => $product->getProductSKU(),
                        "productName" => $product->getProductName(),
                        "productImage" => $product->getImage(),
                        "productCategory" => $categories,
                    );
                }
                $msg = $data;
                $success = true;
            }else{
                $msg = "No data found.";
                $success = false;
            }
        } catch (\Exception $e) {
            $msg = array($e->getMessage());
            $success = false;
        }
        return $this->json(["success" => $success, "msg" => $msg]);
    }

    /**
     * @Route("/product-view-by-sku", name="product_sku_view", methods={"POST"})
     */
    public function productSKUViewAction(Request $request)
    {
        try{
            $sku = $request->get('sku');
            $productList = new DataObject\Product\Listing();
            //$productList->setCondition("ProductSKU LIKE :ProductSKU AND ProductName  :ProductName", ["ProductSKU" => $sku, "ProductName" => $productName]);
            $productList->setCondition("ProductSKU LIKE ?", $sku);
            $productDetails = $productList->load();
            if($productDetails){
                foreach ($productDetails as $key => $product) {
                    $categories = array();
                    foreach ($product->getCategory() as $key => $eachCategory) {
                       $categories[$eachCategory->getCategoryId()] = $eachCategory->getCategoryName() ;
                    }
                    $data[] = array(
                        "productSKU" => $product->getProductSKU(),
                        "productName" => $product->getProductName(),
                        "productImage" => $product->getImage(),
                        "productCategory" => $categories,
                    );
                }
                $msg = $data;
                $success = true;
            }else{
                $msg = "No data found";
                $success = false;
            }
            
        } catch (\Exception $e) {
            $msg = array($e->getMessage());
            $success = false;
        }
        return $this->json(["success" => $success, "msg" => $msg]);
    }


    /**
     * @Route("/product-list", name="product_list", methods={"GET"})
     */
    public function productListAction(Request $request)
    {
        try{
            $products = new DataObject\Product\Listing();
            if($products){
                foreach ($products as $key => $product) {
                    $categories = array();
                    foreach ($product->getCategory() as $key => $eachCategory) {
                       $categories[$eachCategory->getCategoryId()] = $eachCategory->getCategoryName() ;
                    }
                    $data[] = array(
                        "productSKU" => $product->getProductSKU(),
                        "productName" => $product->getProductName(),
                        "productImage" => $product->getImage(),
                        "productCategory" => $categories,
                        );
                }
                $msg = $data;
                $success = true;
            }else{
                $msg = "No data found.";
                $success = false;
            }
        } catch (\Exception $e) {
            $msg = array($e->getMessage());
            $success = false;
        }
        return $this->json(["success" => $success, "msg" => $msg]);
    }

    /**
     * @Route("/category-list", name="category_list", methods={"GET"})
     */
    public function categoryListAction(Request $request)
    {
        $categories = new DataObject\Category\Listing();
        if($categories){
            foreach ($categories as $key => $category) {
                $categoryData[] = array(
                    "categoryId" => $category->getCategoryId(),
                    "categoryName" => $category->getCategoryName(),
                    'parentCategoryName'  => $category->getCategoryParentId(),
                    );
            }
            $success = true;
            $data = $categoryData;
            
        }else{
            $success = false;
            $data = "No data found.";
        }

        return $this->json(["success" => $success, "data" => $data]);
    }


    /**
     * @Route("/classificationStore", name="classification_store", methods={"GET"})
     */
    public function classificationStore(){
        $data = "test";
        //receiving data of a Objectbrick
        // $product = DataObject\Product::getById(4);
        // $tiretype = $product->getBricks()->getTire()->getTiretype();
        return $this->json(["success" => true, "data" => $data]);
    }
    
}