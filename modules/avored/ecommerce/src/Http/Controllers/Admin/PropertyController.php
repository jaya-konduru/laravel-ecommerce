<?php
namespace AvoRed\Ecommerce\Http\Controllers\Admin;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use AvoRed\Framework\DataGrid\Facade as DataGrid;
use AvoRed\Framework\Repository\Product;
use AvoRed\Ecommerce\Http\Requests\PropertyRequest;


class PropertyController extends AdminController
{
    /**
     * AvoRed Product Repository
     *
     * @var \AvoRed\Framework\Repository\Product
     */
    protected $productRepository;

    /**
     * ProductController constructor to Set AvoRed Product Repository Property.
     *
     * @param \AvoRed\Framework\Repository\Product $repository
     * @return void
     */
    public function __construct(Product $repository)
    {
        $this->productRepository = $repository;
    }

    /**
     * Display a listing of the Property.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $dataGrid = DataGrid::model($this->productRepository->propertyModel()->query()->orderBy('id','desc'))
            ->column('id',['sortable' => true])
            ->column('name')
            ->column('identifier')
            ->linkColumn('edit',[], function($model) {
                return "<a href='". route('admin.property.edit', $model->id)."' >Edit</a>";

            })->linkColumn('destroy',[], function($model) {
                return "<form id='admin-property-destroy-".$model->id."'
                                            method='POST'
                                            action='".route('admin.property.destroy', $model->id) ."'>
                                        <input name='_method' type='hidden' value='DELETE' />
                                        ". csrf_field()."
                                        <a href='#'
                                            onclick=\"jQuery('#admin-property-destroy-$model->id').submit()\"
                                            >Destroy</a>
                                    </form>";
            });

        return view('avored-ecommerce::admin.property.index')->with('dataGrid', $dataGrid);
    }

    /**
     * Show the form for creating a new page.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('avored-ecommerce::admin.property.create');
    }

    /**
     * Store a newly created property in database.
     *
     * @param \AvoRed\Ecommerce\Http\Requests\PropertyRequest $request
     *
     * @return \Illuminate\Http\Response
     */
    public function store(PropertyRequest $request)
    {
        $property = $this->productRepository->propertyModel()->create($request->all());

        $this->_saveDropdownOptions($property,$request);
        return redirect()->route('admin.property.index');
    }

    /**
     * Show the form for editing the specified property.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $property = $this->productRepository->propertyModel()->findorfail($id);

        return view('avored-ecommerce::admin.property.edit')->with('model', $property);
    }

    /**
     * Update the specified property in database.
     *
     * @param \AvoRed\Ecommerce\Http\Requests\Property $request
     * @param int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function update(PropertyRequest $request, $id)
    {
        $property = $this->productRepository->propertyModel()->findorfail($id);
        $property->update($request->all());

        $this->_saveDropdownOptions($property,$request);

        return redirect()->route('admin.property.index');
    }

    /**
     * Remove the specified property from storage.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $this->productRepository->propertyModel()->destroy($id);

        return redirect()->route('admin.property.index');
    }



    /**
     * Get the Element Html in Json Response.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function getElementHtml(Request $request)
    {
        $properties = $this->productRepository->propertyModel()->whereIn('id',$request->get('property_id'))->get();

        $tmpString = str_random();
        $view = view('avored-ecommerce::admin.property.get-element')
                        ->with('properties', $properties)
                        ->with('tmpString', $tmpString);


        $json = new JsonResponse(['success' => true,'content' => $view->render()]);
        return $json;
    }

    private function _saveDropdownOptions($property, $request)
    {

        if (null !== $request->get('dropdown-options')) {

            $property->propertyDropdownOptions()->delete();

            foreach ($request->get('dropdown-options') as $key => $val) {
                if ($key == '__RANDOM_STRING__') {
                    continue;
                }

                $property->propertyDropdownOptions()->create($val);

            }
        }
    }


}
