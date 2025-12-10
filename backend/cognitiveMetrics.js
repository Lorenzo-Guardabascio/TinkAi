/**
 * TinkAi Cognitive Metrics System
 * Analizza le risposte per verificare l'aderenza ai principi cognitivi
 */

class CognitiveMetrics {
    constructor() {
        this.metrics = {
            totalInteractions: 0,
            questionsAsked: 0,
            directAnswers: 0,
            reflectivePrompts: 0,
            averageResponseLength: 0,
        };
    }

    /**
     * Analizza una risposta di TinkAi per estrarre metriche cognitive
     */
    analyzeResponse(aiResponse) {
        const analysis = {
            containsQuestion: this.containsQuestion(aiResponse),
            isReflective: this.isReflective(aiResponse),
            isDirect: this.isDirectAnswer(aiResponse),
            length: aiResponse.length,
            questionCount: this.countQuestions(aiResponse),
        };

        // Aggiorna metriche aggregate
        this.metrics.totalInteractions++;
        if (analysis.containsQuestion) this.metrics.questionsAsked++;
        if (analysis.isDirect) this.metrics.directAnswers++;
        if (analysis.isReflective) this.metrics.reflectivePrompts++;
        
        // Aggiorna media lunghezza
        this.metrics.averageResponseLength = 
            ((this.metrics.averageResponseLength * (this.metrics.totalInteractions - 1)) + analysis.length) 
            / this.metrics.totalInteractions;

        return analysis;
    }

    /**
     * Verifica se la risposta contiene domande
     */
    containsQuestion(text) {
        return text.includes('?');
    }

    /**
     * Conta il numero di domande
     */
    countQuestions(text) {
        return (text.match(/\?/g) || []).length;
    }

    /**
     * Verifica se è una risposta riflessiva (contiene termini chiave TinkAi)
     */
    isReflective(text) {
        const reflectiveKeywords = [
            'perché pensi',
            'cosa ne pensi',
            'hai considerato',
            'prova a',
            'rifletti',
            'approfondire',
            'ragion',
            'cosa sai già',
            'parti da',
            'come potresti',
        ];

        return reflectiveKeywords.some(keyword => 
            text.toLowerCase().includes(keyword)
        );
    }

    /**
     * Rileva risposte troppo dirette (potenziale spoon-feeding)
     * Euristica: lunga, senza domande, con elenchi o spiegazioni complete
     */
    isDirectAnswer(text) {
        const hasNoQuestions = !this.containsQuestion(text);
        const isMediumLong = text.length > 200;
        const hasLists = /(\n-|\n\d\.|\n•)/.test(text); // Rileva elenchi
        const hasDefinitiveWords = /(la risposta è|ecco|certamente|ovviamente)/i.test(text);

        return hasNoQuestions && (isMediumLong || hasLists || hasDefinitiveWords);
    }

    /**
     * Calcola il "TinkAi Score" (0-100)
     * 100 = perfettamente cognitivo, 0 = troppo tradizionale
     */
    getTinkAiScore() {
        if (this.metrics.totalInteractions === 0) return 100;

        const questionRatio = this.metrics.questionsAsked / this.metrics.totalInteractions;
        const directRatio = this.metrics.directAnswers / this.metrics.totalInteractions;
        const reflectiveRatio = this.metrics.reflectivePrompts / this.metrics.totalInteractions;

        // Formula pesata
        const score = (
            (questionRatio * 40) +        // 40% peso alle domande
            (reflectiveRatio * 40) +      // 40% peso alla riflessività
            ((1 - directRatio) * 20)      // 20% penalità per risposte dirette
        ) * 100;

        return Math.round(Math.max(0, Math.min(100, score)));
    }

    /**
     * Restituisce un report leggibile
     */
    getReport() {
        const score = this.getTinkAiScore();
        return {
            score,
            totalInteractions: this.metrics.totalInteractions,
            questionsAsked: this.metrics.questionsAsked,
            directAnswers: this.metrics.directAnswers,
            reflectivePrompts: this.metrics.reflectivePrompts,
            averageResponseLength: Math.round(this.metrics.averageResponseLength),
            assessment: this.getAssessment(score),
        };
    }

    /**
     * Valutazione qualitativa del comportamento
     */
    getAssessment(score) {
        if (score >= 80) return 'Eccellente: TinkAi sta funzionando come previsto';
        if (score >= 60) return 'Buono: Comportamento cognitivo attivo';
        if (score >= 40) return 'Sufficiente: Alcune risposte troppo dirette';
        if (score >= 20) return 'Insufficiente: TinkAi si comporta come un assistente tradizionale';
        return 'Critico: Revisione urgente del system prompt richiesta';
    }

    /**
     * Reset metriche
     */
    reset() {
        this.metrics = {
            totalInteractions: 0,
            questionsAsked: 0,
            directAnswers: 0,
            reflectivePrompts: 0,
            averageResponseLength: 0,
        };
    }
}

module.exports = CognitiveMetrics;
