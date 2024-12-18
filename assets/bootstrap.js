import { startStimulusApp } from '@symfony/stimulus-bridge';
import  gptController  from './controllers/gpt_controller.js'

// Registers Stimulus controllers from controllers.json and in the controllers/ directory
const app = startStimulusApp(require.context(
    './controllers',
    true,
    /\.(j|t)sx?$/
));

// register any custom, 3rd party controllers here
app.register('gpt', gptController);
