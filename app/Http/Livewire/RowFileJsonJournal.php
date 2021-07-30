<?php

namespace App\Http\Livewire;

use Livewire\Component;


use App\Models\JsonJournal;
use Illuminate\Support\Str;
use JsonMachine\JsonMachine;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Response;

class RowFileJsonJournal extends Component
{
    public $journal;
    public $export_qty;
    public $export_skip;
    public $export_take;
    public $export_qty_text;
    public $test;
    public $sel_type;
    public $import_force;
    public $row_count;

    public $to_import_type;
    public $to_import_type_title;
    public $to_import_type_warning;


    public $export_qty_category;

    public function mount()
    {

        $this->export_qty = 1;
        $this->sel_type = 2;
        $this->row_count = 0;

        $this->export_qty_category = 1; //per 10k
        //$this->export_qty_category = 2; //per 20k

        $this->to_import_type_title = "";
        $this->to_import_type_warning = "";
    }


    public function set_to_read_json_type($type)
    {
        $this->to_import_type = $type;

        if ($type == "force") {
            $this->import_force = true;
            $this->to_import_type_title = "Force Import";
            $this->to_import_type_warning = "This will delete all old data and will import all data from the file.";
        } elseif ($type == "import") {
            $this->import_force = false;
            $this->to_import_type_title = "Import";
            $this->to_import_type_warning = "This will only import new and updated records.";
        }
    }

    public function export()
    {

        $this->set_export_prop();
        $this->dl_clean_data();
        return Response::download(public_path('exports/' . $this->export_file_name));
    }

    private function set_export_prop()
    {
        if ($this->export_qty_category == 1) {
            $this->export_take = 10000;
            if ($this->export_qty == 1) {
                $this->export_skip = 0;
                $this->export_qty_text = "1-10k";
            }

            if ($this->export_qty == 2) {
                $this->export_skip = 10000;
                $this->export_qty_text = "10-20k";
            }

            if ($this->export_qty == 3) {
                $this->export_skip = 20000;
                $this->export_qty_text = "20-30k";
            }

            if ($this->export_qty == 4) {
                $this->export_skip = 30000;
                $this->export_qty_text = "30-40k";
            }

            if ($this->export_qty == 5) {
                $this->export_skip = 40000;
                $this->export_qty_text = "40-50k";
            }
            if ($this->export_qty == 6) {
                $this->export_skip = 50000;
                $this->export_qty_text = "50-60k";
            }
            if ($this->export_qty == 7) {
                $this->export_skip = 60000;
                $this->export_qty_text = "60-70k";
            }
            if ($this->export_qty == 8) {
                $this->export_skip = 70000;
                $this->export_qty_text = "70-80k";
            }
            if ($this->export_qty == 9) {
                $this->export_skip = 80000;
                $this->export_qty_text = "80-90k";
            }
            if ($this->export_qty == 10) {
                $this->export_skip = 90000;
                $this->export_qty_text = "90-100k";
            }
        } elseif ($this->export_qty_category == 2) {
            $this->export_take = 20000;
            if ($this->export_qty == 1) {
                $this->export_skip = 0;
                $this->export_qty_text = "1-20k";
            }

            if ($this->export_qty == 2) {
                $this->export_skip = 20000;
                $this->export_qty_text = "20-40k";
            }

            if ($this->export_qty == 3) {
                $this->export_skip = 40000;
                $this->export_qty_text = "40-60k";
            }

            if ($this->export_qty == 4) {
                $this->export_skip = 60000;
                $this->export_qty_text = "60-80k";
            }

            if ($this->export_qty == 5) {
                $this->export_skip = 80000;
                $this->export_qty_text = "80-100k";
            }
        }
    }

    private function dl_clean_data()
    {
        $skip = $this->export_skip;
        $take = $this->export_take;

        if ($this->sel_type == 1) {
            $data = JsonJournal::where('upload_id', $this->journal->id)
                ->skip($skip)->take($take)
                ->get();
        }

        if ($this->sel_type == 2) {
            $data = JsonJournal::where('upload_id', $this->journal->id)
                ->skip($skip)->take($take)
                ->where('is_new', true)
                ->orWhere('is_updated', true)
                ->get();
        }



        $rows = [];
        $ctr = 1;

        foreach ($data as $d) {

            if ($d->keywords) {
                $row['bibjson']['keywords'] = json_decode($d->keywords);
            }

            if ($d->subject) {
                $row['bibjson']['subject'] = json_decode($d->subject);
            }
            if ($d->eissn) {
                $row['bibjson']['eissn'] = ($d->eissn);
            }
            if ($d->title) {
                $row['bibjson']['title'] = ($d->title);
            }
            if ($d->ref) {

                $row['bibjson']['ref']['aims_scope'] = json_decode($d->ref)->aims_scope;
            }
            if ($d->language) {
                $row['bibjson']['language'] = json_decode($d->language);
            }

            if ($d->publisher) {
                $row['bibjson']['publisher']['country'] = json_decode($d->publisher)->country;
                $row['bibjson']['publisher']['name'] = json_decode($d->publisher)->name;
            }

            if (!is_null($d->institution)) {
                if (isset(json_decode($d->institution)->name)) {
                    $row['bibjson']['institution']['name'] = json_decode($d->institution)->name;
                }
            }

            array_push($rows, $row);
        }

        $data_file = json_encode($rows, JSON_PRETTY_PRINT);
        $fileName = $this->journal->file_name . "_CLEAN_" .  time() . '.json';
        $fileName = $this->journal->file_name . "_CLEAN_" .  $this->export_qty_text . '.json';

        $fileName2 = $this->journal->file_name;
        File::put(public_path('exports/' . $fileName), $data_file);

        $this->export_file_name = $fileName;

        auth()->user()->logs()->create([
            'action' => 'Export file: ' . $this->export_file_name,
            'type' => 'export-journal',
            'obj' => json_encode([
                'file_name' => $fileName
            ])
        ]);
    }

    public function import_json()
    {
        $this->path = storage_path('app/json/Journal/') . $this->journal->file_name . '.json';
        //$rows = json_decode(file_get_contents($path), true);
        $rows = JsonMachine::fromFile($this->path);

        $limit = 200000;
        //   $limit = 1000;
        $limit_ctr = 0;
        $record_ctr = 0;
        $extracted_ctr = 0;
        $record_new_ctr = 0;
        $record_updated_ctr = 0;

        $import_start = Carbon::now();

        JsonJournal::where('upload_id', $this->journal->id)->update([
            'is_new' => false,
            'is_updated' => false
        ]);

        if ($this->import_force) {
            JsonJournal::where('upload_id', $this->journal->id)->delete();
        }

        $ctr = JsonJournal::where('upload_id', $this->journal->id)->max('ctr') + 1;
        $record_ctr_all = JsonJournal::where('upload_id', $this->journal->id)->count();

        foreach ($rows as $row) {

            $record_ctr++;
            $this->row_count = $record_ctr;

            if (isset($row['bibjson']['subject'])) {
                if (!$this->is_subject_medical(json_encode($row['bibjson']['subject']))) {
                    //if not medical, skip
                    continue;
                }
            }


            $journal = JsonJournal::where('journal_id', $row['id'])->first();
            $journal_exists = false;

            if (!$journal) {

                $journal = new JsonJournal();
                $record_new_ctr++;
            } else {
                //record exists
                $journal_exists = true;
                $last_updated = $journal->last_updated;
            }

            $journal->full_row_obj =  json_encode($row);

            $journal->journal_id = $row['id'];
            $journal->upload_id = $this->journal->id;

            if (isset($row['bibjson']['editorial'])) {
                $journal->editorial = json_encode($row['bibjson']['editorial']);
            }
            if (isset($row['bibjson']['pid_scheme'])) {
                $journal->pid_scheme = json_encode($row['bibjson']['pid_scheme']);
            }
            if (isset($row['bibjson']['copyright'])) {
                $journal->copyright = json_encode($row['bibjson']['copyright']);
            }

            if (isset($row['bibjson']['keywords'])) {
                $journal->keywords = json_encode($row['bibjson']['keywords']);
                $journal->keywords_orig = json_encode($row['bibjson']['keywords']);
            }

            if (isset($row['bibjson']['plagiarism'])) {
                $journal->plagiarism = json_encode($row['bibjson']['plagiarism']);
            }

            if (isset($row['bibjson']['subject'])) {
                $journal->subject = json_encode($row['bibjson']['subject']);
                $journal->subject_orig = json_encode($row['bibjson']['subject']);
            }

            if (isset($row['bibjson']['eissn'])) {
                $journal->eissn =  $row['bibjson']['eissn'];
            }

            if (isset($row['bibjson']['pissn'])) {
                $journal->pissn =  $row['bibjson']['pissn'];
            }

            if (isset($row['bibjson']['language'])) {
                $journal->language =  json_encode($row['bibjson']['language']);
            }


            $journal->title_short = $record_new_ctr;
            if (isset($row['bibjson']['title'])) {
                $journal->title = $row['bibjson']['title'];
                $journal->title_short = Str::substr($journal->title, 0, 180) . ' ' . $record_new_ctr;
            }

            if (isset($row['bibjson']['article'])) {
                $journal->article = json_encode($row['bibjson']['article']);
            }
            if (isset($row['bibjson']['institution'])) {
                $journal->institution = json_encode($row['bibjson']['institution']);
            }

            if (isset($row['bibjson']['preservation'])) {
                $journal->preservation = json_encode($row['bibjson']['preservation']);
            }

            if (isset($row['bibjson']['license'])) {
                $journal->license = json_encode($row['bibjson']['license']);
            }

            if (isset($row['bibjson']['ref'])) {
                $journal->ref = json_encode($row['bibjson']['ref']);
            }

            if (isset($row['bibjson']['apc'])) {
                $journal->apc = json_encode($row['bibjson']['apc']);
            }

            if (isset($row['bibjson']['other_charges'])) {
                $journal->other_charges = json_encode($row['bibjson']['other_charges']);
            }

            if (isset($row['bibjson']['publication_time_weeks'])) {
                $journal->publication_time_weeks = ($row['bibjson']['publication_time_weeks']);
            }

            if (isset($row['bibjson']['deposit_policy'])) {
                $journal->deposit_policy = json_encode($row['bibjson']['deposit_policy']);
            }

            if (isset($row['bibjson']['publisher'])) {
                $journal->publisher = json_encode($row['bibjson']['publisher']);
            }

            if (isset($row['bibjson']['boai'])) {
                $journal->boai = ($row['bibjson']['boai']);
            }
            if (isset($row['bibjson']['waiver'])) {
                $journal->waiver = json_encode($row['bibjson']['waiver']);
            }
            if (isset($row['admin'])) {
                $journal->admin = json_encode($row['admin']);
            }








            $journal->last_updated = date('Y-m-d h:i:s', strtotime($row['last_updated']));
            $journal->created_date = date('Y-m-d h:i:s', strtotime($row['created_date']));


            if ($journal_exists) {
                //compare the last update date
                if (
                    date('Y-m-d h:i:s', strtotime($row['last_updated'])) > $last_updated
                ) {
                    $journal->is_updated = true;
                    $journal->save();

                    $record_updated_ctr++;
                    $extracted_ctr++;
                }
            } else { //not exists,means new
                $journal->is_new = true;
                $journal->ctr = $ctr;
                $journal->save();

                $extracted_ctr++;
            }

            if ($limit <= $record_ctr) { // limit_ctr only includes qualified records
                break;
            }

            $ctr++;
            $limit_ctr++;
        }
        $import_end = Carbon::now();
        $extracted_ctr =  JsonJournal::where('upload_id', $this->journal->id)->count();
        $this->journal->original_record_count = $record_ctr;
        $this->journal->extracted_record_count = $extracted_ctr;
        $this->journal->new_record_count = $record_new_ctr;
        $this->journal->updated_record_count = $record_updated_ctr;
        $this->journal->import_start = $import_start;
        $this->journal->import_end = $import_end;


        $this->journal->save();



        auth()->user()->logs()->create([
            'action' => 'Import file: ' . $this->journal->file_name . ".json",
            'type' => 'import-journal',
            'obj' => json_encode($this->journal)
        ]);
    }

    private function is_subject_medical($subjects) // accepts json
    {


        $valid_subjects = [

            'Medicine',

            //----Medicine
            'Dentistry',
            'Dermatology',
            'Gynecology and obstetrics',
            'Homeopathy',
            'Internal medicine',
            'Infectious and parasitic diseases',
            'Medical emergencies. Critical care. Intensive care. First aid',
            'Neoplasms. Tumors. Oncology. Including cancer and carcinogens',
            'Neurosciences. Biological psychiatry. Neuropsychiatry',
            'Neurology. Diseases of the nervous system',
            'Psychiatry',
            'Therapeutics. Psychotherapy',
            'Special situations and conditions',
            'Arctic medicine. Tropical medicine',
            'Geriatrics',
            'Industrial medicine. Industrial hygiene',
            'Sports medicine',
            'Specialties of internal medicine',
            'Diseases of the blood and blood-forming organs',
            'Diseases of the circulatory (Cardiovascular) system',
            'Diseases of the digestive system. Gastroenterology',
            'Diseases of the endocrine glands. Clinical endocrinology',
            'Diseases of the genitourinary system. Urology',
            'Diseases of the musculoskeletal system',
            'Diseases of the respiratory system',
            'Immunologic diseases. Allergy',
            'Nutritional diseases. Deficiency diseases',

            'Medicine (General)',
            'Computer applications to medicine. Medical informatics',
            'General works',
            'History of medicine. Medical expeditions',
            'Medical philosophy. Medical ethics',
            'Medical physics. Medical radiology. Nuclear medicine',
            'Medical technology',

            'Medical',
            'Nursing',
            'Ophthalmology',
            'Other systems of medicine',

            'Chiropractic',
            'Mental healing',
            'Miscellaneous systems and treatments',
            'Osteopathy',

            'Optics',
            'Otorhinolaryngology',
            'Pathology',
            'Pediatrics',
            'Pharmacy and materia medica',
            'Public aspects of medicine',
            'Toxicology. Poisons',
            'Anesthesiology',
            'Orthopedic surgery',
            'Therapeutics. Pharmacology',
            'Philosophy. Psychology.', //no found
            'Aesthetics',
            'Psychology',
            'Consciousness. Cognition',

            'Science',
            'Biology (General)',
            'Ecology',
            'Genetics',
            'Life',
            'Reproduction',

            'Chemistry',
            'Analytical chemistry',
            'Organic chemistry',
            'Biochemistry',
            'Human anatomy',
            'Microbiology',
            'Microbial ecology',

            'Physiology',
            'Biochemistry',
            'Neurophysiology and neuropsychology',

            'Zoology'


        ];



        $subjects = json_decode($subjects);
        foreach ($subjects as $subject) {
            if (in_array($subject->term, $valid_subjects)) {
                return true;
            }
        }

        return false;
    }


    public function render()
    {
        return view('livewire.row-file-json-journal');
    }
}
