import { useState } from 'react'
import bitsFetch from '../Utils/bitsFetch'
import { __ } from '../Utils/i18nwrap'
import LoaderSm from './Loaders/LoaderSm'
import Modal from './Utilities/Modal'
import ReactStars from "react-rating-stars-component";
import { $integrationDetails } from '../Utils/GlobalStates'
import { useRecoilState } from 'recoil'


export default function ReviewsEdit(props) {
    const [isLoading, setisLoading] = useState(false)
    const [integConfig, setIntegConfig] = useRecoilState($integrationDetails);
    const [formDetails, setFormDetails] = useState(() => {
        const filteredData = props.tableData.filter(item => (
            item.id === props.rowId
        ))
        return JSON.parse(filteredData[0].form_details)
    }
    )

    const ratingChange = (newRating) => {
        setFormDetails({ ...formDetails, ['star']: newRating })
    };

    const handleChange = (e) => {
        const { name, value } = e.target
        setFormDetails({ ...formDetails, [name]: value })
    }

    const phrases = formDetails?.phrases
    const handlePhrases = (e) => {
        if (!phrases.includes(e.target.value)) {
            phrases.push(e.target.value)
        }
        else {
            const phraseIndex = phrases.indexOf(e.target.value)
            phrases.splice(phraseIndex, 1)
        }
        setFormDetails({ ...formDetails, ['phrases']: phrases })

    }

    const updateData = () => {
        setisLoading(true)
        const data = {};
        data.inputData = formDetails
        data.editRowId = props.rowId
        bitsFetch(data, 'review_update')
            .then(response => {
                console.log(response)
                props.setTableData(response.data)
                setisLoading(false)
                if (response.success) {
                    props.setSnackbar({ show: true, msg: __('Data Updated Successfully', 'bitwelzp') })
                    props.close(false)
                    bitsFetch(integConfig?.integ_config?.auth_details, 'get_peoples_forms')
                        .then(response => {
                            console.log(response)
                        })
                }

                else {
                    setisLoading(false)
                    props.close(false)
                    props.setSnackbar({ show: true, msg: __('Updating Failed', 'bitwelzp') })
                }
            })
    }


    function SaveBtn() {
        return (
            <button onClick={updateData} disabled={isLoading} type="button" className="btn btn-md blue btcd-mdl-hdr-btn">
                Update
                {isLoading && <LoaderSm size={20} clr="#fff" className="ml-2" />}
            </button>
        )
    }
    return (
        <Modal
            hdrActn={<SaveBtn />}
            lg
            show={true}
            setModal={props.close}
            title={__('Edit', 'bitform')}
        >
            <form className="riviews-form">
                <label>Please rate your overall experience with your therapist:</label>
                <ReactStars
                    count={5}
                    onChange={ratingChange}
                    size={30}
                    activeColor="#ffd700"
                    value={formDetails?.star}
                />
                <label>Please select which of the following phrases describe your therapist:</label>
                <div class="control-group">
                    <div class="control_indicator">
                        <input type="checkbox" name="phrases" id='phrases' value='Knowledgeable'
                            onChange={handlePhrases} checked={formDetails?.phrases.includes('Knowledgeable')} />
                        <label class="control control--checkbox">Knowledgeable </label>
                    </div>
                    <div class="control_indicator">

                        <input type="checkbox" name="phrases" id='phrases' value='Supportive' onChange={handlePhrases} checked={formDetails?.phrases.includes('Supportive')} />
                        <label class="control control--checkbox">Supportive  </label>
                    </div>
                    <div class="control_indicator">

                        <input type="checkbox" name="phrases" id='phrases' value='Friendly' onChange={handlePhrases} checked={formDetails?.phrases.includes('Friendly')} />
                        <label class="control control--checkbox">Friendly  </label>
                    </div>
                    <div class="control_indicator">

                        <input type="checkbox" name="phrases" id='phrases' value='Helpful' onChange={handlePhrases} checked={formDetails?.phrases.includes('Helpful')} />
                        <label class="control control--checkbox">Helpful  </label>
                    </div>
                    <div class="control_indicator">

                        <input type="checkbox" name="phrases" id='phrases' value='Understanding' onChange={handlePhrases} checked={formDetails?.phrases.includes('Understanding')} />
                        <label class="control control--checkbox">Understanding  </label>
                    </div>
                    <div class="control_indicator">

                        <input type="checkbox" name="phrases" id='phrases' value='A good fit for me' onChange={handlePhrases} checked={formDetails?.phrases.includes('A good fit for me')} />
                        <label class="control control--checkbox">A good fit for me  </label>
                    </div>
                    <div class="control_indicator">

                        <input type="checkbox" name="phrases" id='phrases' value='Compassionate' onChange={handlePhrases} checked={formDetails?.phrases.includes('Compassionate')} />
                        <label class="control control--checkbox">Compassionate  </label>
                    </div>
                    <div class="control_indicator">

                        <input type="checkbox" name="phrases" id='phrases' value='Professional' onChange={handlePhrases} checked={formDetails?.phrases.includes('Professional')} />
                        <label class="control control--checkbox">Professional  </label>
                    </div>
                    <div class="control_indicator">
                        <input type="checkbox" name="phrases" id='phrases' value='Patient' onChange={handlePhrases} checked={formDetails?.phrases.includes('Patient')} />
                        <label class="control control--checkbox">Patient  </label>
                    </div>
                    <div class="control_indicator">

                        <input type="checkbox" name="phrases" id='phrases' value='Flexible' onChange={handlePhrases} checked={formDetails?.phrases.includes('Flexible')} />
                        <label class="control control--checkbox">Flexible  </label>
                    </div>
                    <div class="control_indicator">

                        <input type="checkbox" name="phrases" id='phrases' value='Competent' onChange={handlePhrases} checked={formDetails?.phrases.includes('Competent')} />
                        <label class="control control--checkbox">Competent  </label>
                    </div>
                    <div class="control_indicator">

                        <input type="checkbox" name="phrases" id='phrases' value='Empathetic' onChange={handlePhrases} checked={formDetails?.phrases.includes('Empathetic')} />
                        <label class="control control--checkbox">Empathetic  </label>
                    </div>
                </div>
                <span>Please describe your experience with your therapist:</span>
                <label>Title:</label>
                <input type="text" name="title" onChange={handleChange} value={formDetails?.title} />
                <label>Description:</label>
                <textarea name="desc" rows="5" onChange={handleChange} value={formDetails?.desc}></textarea>
                <span>What age range are you?</span>
                <div className="age-range">
                    <input type="radio" name="age" value="under 20" onChange={handleChange} checked={formDetails?.age === 'under 20'} />Under 20
                    <input type="radio" name="age" value="20-30" onChange={handleChange} checked={formDetails?.age === '20-30'} />20 - 30
                    <input type="radio" name="age" value="30-40" onChange={handleChange} checked={formDetails?.age === '30-40'} />30 - 40
                    <input type="radio" name="age" value="40-60" onChange={handleChange} checked={formDetails?.age === '40-60'} />40 - 60
                    <input type="radio" name="age" value="60-70" onChange={handleChange} checked={formDetails?.age === '60-70'} />60 - 70
                    <input type="radio" name="age" value="70+" onChange={handleChange} checked={formDetails?.age === '70+'} />70+
                </div>
                <span>What gender are you?</span>
                <div className="gender">
                    <input type="radio" name="gender" value="Male" onChange={handleChange} checked={formDetails?.gender === 'Male'} /> Male
                    <input type="radio" name="gender" value="Female" onChange={handleChange} checked={formDetails?.gender === 'Female'} /> Female
                    <input type="radio" name="gender" value="Non-Binary" onChange={handleChange} checked={formDetails?.gender === 'Non-Binary'} /> Non-Binary
                </div>
                <label>Please enter your first name:</label>
                <input type="text" name="fname" onChange={handleChange} value={formDetails?.fname} />
                <label>Please enter your last initial:</label>
                <input type="text" name="lname" onChange={handleChange} value={formDetails?.lname} />
            </form>

        </Modal>
    )
}
